/* worthly-screens.jsx — All app screens. */

const { useState, useEffect, useRef, useMemo } = React;

// ═════════════════════════════════════════════════════════════
// ONBOARDING — 3-slide carousel with paginator
// ═════════════════════════════════════════════════════════════
function OnboardingScreen({ onDone }) {
  const slides = [
    {
      eyebrow: "01 / Worthly",
      headline: <>Is it <em>actually</em> worth it?</>,
      body: "Snap a photo or paste a product name. Worthly tells you if it's a good buy — right now.",
      art: "scan",
    },
    {
      eyebrow: "02 / What you get",
      headline: <>A friendly second opinion.</>,
      body: "Like asking the friend who reads every review so you don't have to. Honest. Specific. Yours.",
      art: "verdict",
    },
    {
      eyebrow: "03 / How it works",
      headline: <>Buy. Wait. Skip.</>,
      body: "Three verdicts. One clear recommendation per product, backed by fresh prices and real reviews.",
      art: "trio",
    },
  ];
  const [i, setI] = useState(0);
  const s = slides[i];

  return (
    <div style={{
      height: '100%', display: 'flex', flexDirection: 'column',
      background: 'var(--w-cream)',
      padding: '70px 28px 36px',
    }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <WorthlyMark size={22} />
        <button onClick={onDone} style={{
          background: 'transparent', border: 0, cursor: 'pointer',
          fontFamily: 'var(--font-mono)', fontSize: 11, color: 'var(--w-muted)',
          letterSpacing: '0.08em', textTransform: 'uppercase',
        }}>Skip</button>
      </div>

      <div style={{
        flex: 1, display: 'flex', flexDirection: 'column',
        justifyContent: 'center', alignItems: 'center', textAlign: 'center',
        padding: '0 4px', gap: 28,
      }}>
        <OnboardingArt kind={s.art} />
        <div>
          <div style={{
            fontFamily: 'var(--font-mono)', fontSize: 11, fontWeight: 500,
            letterSpacing: '0.14em', textTransform: 'uppercase',
            color: 'var(--w-muted)', marginBottom: 16,
          }}>{s.eyebrow}</div>
          <h1 style={{
            fontFamily: 'var(--font-display)', fontWeight: 400,
            fontSize: 40, lineHeight: 1.05, letterSpacing: '-0.01em',
            color: 'var(--w-ink)', margin: '0 0 16px',
          }}>{s.headline}</h1>
          <p style={{
            fontSize: 15, lineHeight: 1.5, color: 'var(--w-muted)',
            margin: 0, maxWidth: 300,
          }}>{s.body}</p>
        </div>
      </div>

      {/* paginator */}
      <div style={{ display: 'flex', justifyContent: 'center', gap: 6, marginBottom: 18 }}>
        {slides.map((_, j) => (
          <button key={j} onClick={() => setI(j)} style={{
            width: j === i ? 22 : 6, height: 6, borderRadius: 999,
            background: j === i ? 'var(--w-ink)' : 'var(--w-line-2)',
            border: 0, cursor: 'pointer', transition: 'width 200ms',
          }} />
        ))}
      </div>

      <PrimaryButton onClick={() => i < slides.length - 1 ? setI(i + 1) : onDone()}>
        {i < slides.length - 1 ? 'Continue' : 'Get started'} <Icon.ArrowRight s={16} />
      </PrimaryButton>
      <button onClick={onDone} style={{
        appearance: 'none', background: 'transparent', border: 0,
        marginTop: 10, padding: 10, color: 'var(--w-muted)',
        fontFamily: 'var(--font-ui)', fontSize: 13, cursor: 'pointer',
      }}>
        I already have an account
      </button>
    </div>
  );
}

function OnboardingArt({ kind }) {
  const box = {
    width: 260, height: 220, position: 'relative',
    display: 'flex', alignItems: 'center', justifyContent: 'center',
  };
  if (kind === 'scan') {
    return (
      <div style={box}>
        {/* Phone scanning a product */}
        <div style={{
          width: 180, height: 200, background: 'var(--w-paper)',
          border: '0.5px solid var(--w-line)', borderRadius: 18,
          position: 'relative', overflow: 'hidden',
          boxShadow: '0 12px 40px rgba(20,19,15,0.08)',
        }}>
          {/* Product silhouette */}
          <div style={{
            position: 'absolute', inset: '22% 18% 22% 18%',
            background: 'linear-gradient(140deg, #383532 0%, #1A1815 100%)',
            borderRadius: 14,
          }} />
          {/* Scan corners */}
          {[{t:14,l:14,r:0,b:0},{t:14,r:14,l:0,b:0},{b:14,l:14,t:0,r:0},{b:14,r:14,t:0,l:0}].map((p,i)=>(
            <div key={i} style={{
              position: 'absolute',
              top: p.t || 'auto', left: p.l || 'auto',
              right: p.r || 'auto', bottom: p.b || 'auto',
              width: 22, height: 22,
              borderTop: p.t ? '2px solid var(--w-buy)' : 0,
              borderBottom: p.b ? '2px solid var(--w-buy)' : 0,
              borderLeft: p.l ? '2px solid var(--w-buy)' : 0,
              borderRight: p.r ? '2px solid var(--w-buy)' : 0,
              borderRadius: 4,
            }} />
          ))}
          {/* Scanning line */}
          <div style={{
            position: 'absolute', left: 14, right: 14, top: '50%',
            height: 2, background: 'var(--w-buy)',
            boxShadow: '0 0 12px var(--w-buy)',
            animation: 'scan 2.4s ease-in-out infinite',
          }} />
        </div>
        <style>{`@keyframes scan {
          0%, 100% { transform: translateY(-60px); }
          50% { transform: translateY(60px); }
        }`}</style>
      </div>
    );
  }
  if (kind === 'verdict') {
    return (
      <div style={box}>
        <div style={{ position: 'relative', width: 240, height: 200 }}>
          {/* Stacked verdict cards */}
          {[
            { v: 'skip', y: 0, x: -36, r: -8 },
            { v: 'wait', y: 14, x: 0, r: 0 },
            { v: 'buy', y: 28, x: 36, r: 8 },
          ].map((c, i) => {
            const vd = window.WORTHLY.verdicts[c.v];
            return (
              <div key={c.v} style={{
                position: 'absolute', top: c.y, left: '50%',
                transform: `translateX(calc(-50% + ${c.x}px)) rotate(${c.r}deg)`,
                width: 130, padding: 14,
                background: 'var(--w-paper)',
                border: '0.5px solid var(--w-line)',
                borderRadius: 14,
                boxShadow: '0 8px 24px rgba(20,19,15,0.08)',
              }}>
                <VerdictPill verdict={c.v} size="sm" />
                <div style={{
                  fontFamily: 'var(--font-display)', fontStyle: 'italic',
                  fontSize: 22, lineHeight: 1.1, marginTop: 10,
                  color: vd.color,
                }}>{vd.long}</div>
              </div>
            );
          })}
        </div>
      </div>
    );
  }
  // trio
  return (
    <div style={box}>
      <div style={{ display: 'flex', gap: 12 }}>
        {['skip', 'wait', 'buy'].map(v => {
          const vd = window.WORTHLY.verdicts[v];
          return (
            <div key={v} style={{
              width: 72, height: 96,
              background: vd.soft, color: vd.color,
              border: `0.5px solid ${vd.color}33`,
              borderRadius: 14,
              display: 'flex', flexDirection: 'column',
              alignItems: 'center', justifyContent: 'center',
              gap: 8,
            }}>
              <div style={{ width: 14, height: 14, borderRadius: '50%', background: vd.dot }} />
              <div style={{
                fontFamily: 'var(--font-mono)', fontSize: 11, fontWeight: 600,
                letterSpacing: '0.1em',
              }}>{vd.code}</div>
            </div>
          );
        })}
      </div>
    </div>
  );
}

// ═════════════════════════════════════════════════════════════
// LOGIN
// ═════════════════════════════════════════════════════════════
function LoginScreen({ onLogin, onBack }) {
  const [email, setEmail] = useState('hi@example.com');
  const [pw, setPw] = useState('••••••••');
  const [focus, setFocus] = useState(null);

  return (
    <div style={{
      height: '100%', display: 'flex', flexDirection: 'column',
      background: 'var(--w-cream)',
      padding: '70px 28px 28px',
    }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <button onClick={onBack} style={iconBtn()}><Icon.ChevronLeft s={20} /></button>
        <WorthlyMark size={20} />
        <span style={{ width: 32 }} />
      </div>

      <div style={{ marginTop: 48 }}>
        <div style={{
          fontFamily: 'var(--font-mono)', fontSize: 11, fontWeight: 500,
          letterSpacing: '0.14em', textTransform: 'uppercase',
          color: 'var(--w-muted)', marginBottom: 12,
        }}>Sign in</div>
        <h1 style={{
          fontFamily: 'var(--font-display)', fontWeight: 400,
          fontSize: 38, lineHeight: 1.05, letterSpacing: '-0.01em',
          color: 'var(--w-ink)', margin: '0 0 6px',
        }}>Welcome back.</h1>
        <p style={{ fontSize: 14, color: 'var(--w-muted)', margin: 0, lineHeight: 1.5 }}>
          Sign in to keep your analysis history and saved products.
        </p>
      </div>

      <div style={{ marginTop: 32, display: 'flex', flexDirection: 'column', gap: 12 }}>
        <LoginField label="Email" value={email} onChange={setEmail} type="email"
          focused={focus === 'email'} onFocus={() => setFocus('email')} onBlur={() => setFocus(null)} />
        <LoginField label="Password" value={pw} onChange={setPw} type="password"
          focused={focus === 'pw'} onFocus={() => setFocus('pw')} onBlur={() => setFocus(null)} />
      </div>

      <button style={{
        appearance: 'none', background: 'transparent', border: 0,
        alignSelf: 'flex-end', marginTop: 10, padding: 4,
        fontFamily: 'var(--font-ui)', fontSize: 12, color: 'var(--w-muted)',
        cursor: 'pointer',
      }}>Forgot password?</button>

      <div style={{ marginTop: 'auto', display: 'flex', flexDirection: 'column', gap: 10 }}>
        <PrimaryButton onClick={onLogin}>Sign in</PrimaryButton>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '12px 0' }}>
          <div style={{ flex: 1, height: 1, background: 'var(--w-line)' }} />
          <span style={{
            fontFamily: 'var(--font-mono)', fontSize: 10, color: 'var(--w-muted)',
            letterSpacing: '0.12em', textTransform: 'uppercase',
          }}>Or continue with</span>
          <div style={{ flex: 1, height: 1, background: 'var(--w-line)' }} />
        </div>
        <div style={{ display: 'flex', gap: 10 }}>
          <SsoButton onClick={onLogin}><Icon.Apple s={18} /> Apple</SsoButton>
          <SsoButton onClick={onLogin}><Icon.Google s={18} /> Google</SsoButton>
        </div>
        <div style={{
          textAlign: 'center', marginTop: 16, fontSize: 13,
          color: 'var(--w-muted)',
        }}>
          New here? <button onClick={onLogin} style={{
            appearance: 'none', background: 'transparent', border: 0, padding: 0,
            color: 'var(--w-ink)', fontWeight: 500, cursor: 'pointer',
            textDecoration: 'underline', textUnderlineOffset: 3,
          }}>Create an account</button>
        </div>
      </div>
    </div>
  );
}

function LoginField({ label, value, onChange, type, focused, onFocus, onBlur }) {
  return (
    <label style={{
      display: 'block', position: 'relative',
      border: focused ? '1px solid var(--w-ink)' : '1px solid var(--w-line-2)',
      borderRadius: 12, padding: '10px 14px 10px',
      background: 'var(--w-paper)',
      transition: 'border-color 120ms',
    }}>
      <div style={{
        fontFamily: 'var(--font-mono)', fontSize: 10, fontWeight: 500,
        letterSpacing: '0.1em', textTransform: 'uppercase',
        color: 'var(--w-muted)', marginBottom: 2,
      }}>{label}</div>
      <input type={type} value={value}
        onFocus={onFocus} onBlur={onBlur}
        onChange={e => onChange(e.target.value)}
        style={{
          width: '100%', border: 0, outline: 0, background: 'transparent',
          fontFamily: type === 'password' ? 'var(--font-mono)' : 'var(--font-ui)',
          fontSize: 15, color: 'var(--w-ink)',
        }} />
    </label>
  );
}

function SsoButton({ children, onClick }) {
  return (
    <button onClick={onClick} style={{
      flex: 1, appearance: 'none',
      background: 'var(--w-paper)', border: '0.5px solid var(--w-line-2)',
      borderRadius: 14, height: 48,
      display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
      fontFamily: 'var(--font-ui)', fontSize: 14, fontWeight: 500,
      color: 'var(--w-ink)', cursor: 'pointer',
    }}>{children}</button>
  );
}

function iconBtn() {
  return {
    appearance: 'none', border: '1px solid var(--w-line)', background: 'transparent',
    width: 32, height: 32, borderRadius: 999,
    display: 'flex', alignItems: 'center', justifyContent: 'center',
    cursor: 'pointer', color: 'var(--w-ink)',
  };
}

// ═════════════════════════════════════════════════════════════
// HOME — text input primary, camera secondary, recent + suggestions
// ═════════════════════════════════════════════════════════════
function HomeScreen({ onAnalyze, onOpenHistoryItem, query, setQuery }) {
  const inputRef = useRef(null);
  const [focused, setFocused] = useState(false);

  const submit = () => {
    if (!query.trim()) return;
    onAnalyze({ kind: 'text', query: query.trim() });
  };

  return (
    <div style={{ padding: '64px 0 18px' }}>
      {/* Header */}
      <div style={{
        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
        padding: '0 22px', marginBottom: 22,
      }}>
        <WorthlyMark size={22} />
        <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
          <span style={{
            fontFamily: 'var(--font-mono)', fontSize: 10,
            color: 'var(--w-muted)', letterSpacing: '0.1em',
          }}>32 / 50</span>
          <div style={{
            padding: '4px 8px', background: 'var(--w-paper)',
            border: '0.5px solid var(--w-line)', borderRadius: 999,
            fontFamily: 'var(--font-mono)', fontSize: 10, fontWeight: 500,
            letterSpacing: '0.08em', color: 'var(--w-ink)',
          }}>FREE</div>
        </div>
      </div>

      {/* Greeting */}
      <div style={{ padding: '0 22px', marginBottom: 24 }}>
        <div style={{
          fontFamily: 'var(--font-mono)', fontSize: 11, fontWeight: 500,
          letterSpacing: '0.14em', textTransform: 'uppercase',
          color: 'var(--w-muted)', marginBottom: 10,
        }}>Good afternoon, Yan</div>
        <h1 style={{
          fontFamily: 'var(--font-display)', fontWeight: 400,
          fontSize: 36, lineHeight: 1.05, letterSpacing: '-0.01em',
          color: 'var(--w-ink)', margin: 0,
        }}>
          Should you<br /><span style={{ fontStyle: 'italic' }}>actually</span> buy it?
        </h1>
      </div>

      {/* Composer */}
      <div style={{ padding: '0 18px', marginBottom: 12 }}>
        <div style={{
          background: 'var(--w-paper)',
          border: focused ? '1px solid var(--w-ink)' : '0.5px solid var(--w-line-2)',
          borderRadius: 18,
          padding: '14px 14px 12px',
          transition: 'border-color 120ms',
        }}>
          <textarea
            ref={inputRef}
            value={query}
            onChange={e => setQuery(e.target.value)}
            onFocus={() => setFocused(true)}
            onBlur={() => setFocused(false)}
            onKeyDown={e => {
              if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); submit(); }
            }}
            placeholder="Ask about any product…"
            rows={2}
            style={{
              width: '100%', border: 0, outline: 0, background: 'transparent',
              resize: 'none', fontFamily: 'var(--font-ui)', fontSize: 16,
              lineHeight: 1.4, color: 'var(--w-ink)',
              minHeight: 44,
            }}
          />
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 6 }}>
            <div style={{ display: 'flex', gap: 6 }}>
              <ComposerChip icon={<Icon.Camera s={16} />} onClick={() => onAnalyze({ kind: 'image' })} />
              <ComposerChip icon={<Icon.Mic s={16} />} />
            </div>
            <button onClick={submit} disabled={!query.trim()} style={{
              appearance: 'none', border: 0, cursor: query.trim() ? 'pointer' : 'default',
              background: query.trim() ? 'var(--w-ink)' : 'var(--w-line-2)',
              color: query.trim() ? '#FAF8F2' : 'var(--w-muted)',
              width: 36, height: 36, borderRadius: 999,
              display: 'flex', alignItems: 'center', justifyContent: 'center',
            }}>
              <Icon.ArrowRight s={16} />
            </button>
          </div>
        </div>
      </div>

      {/* Suggestions */}
      <div style={{ padding: '14px 22px 8px' }}>
        <SectionLabel>Try one</SectionLabel>
      </div>
      <div style={{
        display: 'flex', gap: 8, padding: '0 22px 4px',
        overflowX: 'auto', overflowY: 'hidden',
        scrollbarWidth: 'none',
      }}>
        {window.WORTHLY.suggestions.map((s, i) => (
          <button key={i} onClick={() => { setQuery(s); setTimeout(() => onAnalyze({ kind: 'text', query: s }), 80); }} style={{
            appearance: 'none', flexShrink: 0,
            background: 'transparent',
            border: '0.5px solid var(--w-line-2)',
            borderRadius: 999, padding: '8px 14px',
            fontFamily: 'var(--font-ui)', fontSize: 13,
            color: 'var(--w-ink-2)', cursor: 'pointer',
            whiteSpace: 'nowrap',
          }}>{s}</button>
        ))}
        <div style={{ width: 8, flexShrink: 0 }} />
      </div>

      {/* Recent */}
      <div style={{ padding: '24px 22px 8px', display: 'flex', justifyContent: 'space-between', alignItems: 'baseline' }}>
        <SectionLabel>Recent analyses</SectionLabel>
        <button style={{
          appearance: 'none', background: 'transparent', border: 0, padding: 0,
          fontFamily: 'var(--font-ui)', fontSize: 12, color: 'var(--w-ink)',
          cursor: 'pointer', textDecoration: 'underline', textUnderlineOffset: 3,
        }}>View all</button>
      </div>
      <div style={{ padding: '6px 18px 0', display: 'flex', flexDirection: 'column', gap: 8 }}>
        {window.WORTHLY.history.slice(0, 3).map(h => (
          <HistoryRow key={h.id} item={h} onClick={() => onOpenHistoryItem(h)} />
        ))}
      </div>
    </div>
  );
}

function ComposerChip({ icon, label, onClick }) {
  return (
    <button onClick={onClick} style={{
      appearance: 'none', border: '0.5px solid var(--w-line)',
      background: 'transparent', borderRadius: 999,
      height: 32, padding: label ? '0 12px' : 0, minWidth: 32,
      display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6,
      fontFamily: 'var(--font-ui)', fontSize: 12, color: 'var(--w-ink-2)',
      cursor: 'pointer',
    }}>{icon}{label}</button>
  );
}

function HistoryRow({ item, onClick }) {
  const p = window.WORTHLY.products[item.product_id];
  return (
    <button onClick={onClick} style={{
      appearance: 'none', background: 'var(--w-paper)',
      border: '0.5px solid var(--w-line)', borderRadius: 14,
      padding: 12, textAlign: 'left', cursor: 'pointer',
      display: 'flex', gap: 12, alignItems: 'center', width: '100%',
    }}>
      <ProductImage product={p || { brand: item.product_name.split(' ')[0] }} size={48} radius={10} />
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 4 }}>
          <VerdictPill verdict={item.verdict} size="sm" />
          <span style={{
            fontFamily: 'var(--font-mono)', fontSize: 10, color: 'var(--w-muted-2)',
            letterSpacing: '0.06em',
          }}>{item.relative}</span>
        </div>
        <div style={{
          fontSize: 14, fontWeight: 500, color: 'var(--w-ink)',
          whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis',
          marginBottom: 2,
        }}>{item.product_name}</div>
        <div style={{
          fontSize: 12, color: 'var(--w-muted)', lineHeight: 1.4,
          display: '-webkit-box', WebkitLineClamp: 1, WebkitBoxOrient: 'vertical',
          overflow: 'hidden',
        }}>{item.summary}</div>
      </div>
      <Icon.ChevronRight s={14} c="var(--w-muted-2)" />
    </button>
  );
}

// ═════════════════════════════════════════════════════════════
// ANALYZING — animated multi-step loader
// ═════════════════════════════════════════════════════════════
function AnalyzingScreen({ query, kind, onDone }) {
  const steps = [
    { k: 'identify', label: 'Identifying product', ms: 700 },
    { k: 'search', label: 'Searching the web', ms: 900 },
    { k: 'reviews', label: 'Reading reviews', ms: 800 },
    { k: 'compare', label: 'Comparing alternatives', ms: 700 },
    { k: 'verdict', label: 'Forming a verdict', ms: 600 },
  ];
  const [step, setStep] = useState(0);

  useEffect(() => {
    if (step >= steps.length) {
      const t = setTimeout(onDone, 300);
      return () => clearTimeout(t);
    }
    const t = setTimeout(() => setStep(step + 1), steps[step].ms);
    return () => clearTimeout(t);
  }, [step]);

  return (
    <div style={{
      height: '100%', display: 'flex', flexDirection: 'column',
      background: 'var(--w-cream)',
    }}>
      <ScreenHeader transparent eyebrow={kind === 'image' ? 'Image analysis' : 'Text analysis'}
        title="Worthly is thinking…" />

      <div style={{ flex: 1, padding: '0 28px 28px', display: 'flex', flexDirection: 'column' }}>
       <div style={{ marginTop: 24, padding: '0 4px' }}>
        {kind === 'text' && query && (
          <Card padding={14} style={{ marginBottom: 28 }}>
            <div style={{
              fontFamily: 'var(--font-mono)', fontSize: 10, color: 'var(--w-muted)',
              letterSpacing: '0.12em', textTransform: 'uppercase', marginBottom: 6,
            }}>Your question</div>
            <div style={{ fontSize: 14, lineHeight: 1.5, color: 'var(--w-ink)' }}>"{query}"</div>
          </Card>
        )}
        {kind === 'image' && (
          <div style={{
            marginBottom: 28, padding: 16,
            background: 'var(--w-paper)', borderRadius: 14,
            border: '0.5px solid var(--w-line)',
            display: 'flex', gap: 12, alignItems: 'center',
          }}>
            <ProductImage product={window.WORTHLY.products["mx-master-3s"]} size={72} radius={10} />
            <div>
              <div style={{
                fontFamily: 'var(--font-mono)', fontSize: 10, color: 'var(--w-muted)',
                letterSpacing: '0.12em', textTransform: 'uppercase', marginBottom: 4,
              }}>Image uploaded</div>
              <div style={{ fontSize: 14, color: 'var(--w-ink)' }}>IMG_2843.jpeg</div>
              <div style={{ fontSize: 12, color: 'var(--w-muted)', marginTop: 2 }}>2.4 MB · 4032×3024</div>
            </div>
          </div>
        )}

        {/* Step list */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
          {steps.map((s, i) => {
            const status = i < step ? 'done' : i === step ? 'active' : 'idle';
            return (
              <div key={s.k} style={{
                display: 'flex', alignItems: 'center', gap: 14,
                padding: '14px 4px',
                borderBottom: '0.5px solid var(--w-line)',
                opacity: status === 'idle' ? 0.4 : 1,
                transition: 'opacity 200ms',
              }}>
                <div style={{ width: 22, height: 22, position: 'relative' }}>
                  {status === 'done' && (
                    <div style={{
                      width: 22, height: 22, borderRadius: '50%',
                      background: 'var(--w-buy)', color: '#FAF8F2',
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                    }}>
                      <Icon.Check s={12} />
                    </div>
                  )}
                  {status === 'active' && (
                    <div style={{
                      width: 22, height: 22, borderRadius: '50%',
                      border: '1.5px solid var(--w-line-2)',
                      borderTopColor: 'var(--w-ink)',
                      animation: 'spin 700ms linear infinite',
                    }} />
                  )}
                  {status === 'idle' && (
                    <div style={{
                      width: 22, height: 22, borderRadius: '50%',
                      border: '1px solid var(--w-line-2)',
                    }} />
                  )}
                </div>
                <div style={{
                  flex: 1, fontSize: 14,
                  color: status === 'active' ? 'var(--w-ink)' : 'var(--w-ink-2)',
                  fontWeight: status === 'active' ? 500 : 400,
                }}>{s.label}</div>
                <span style={{
                  fontFamily: 'var(--font-mono)', fontSize: 10,
                  color: 'var(--w-muted)', letterSpacing: '0.08em',
                }}>
                  {String(i + 1).padStart(2, '0')}/{String(steps.length).padStart(2, '0')}
                </span>
              </div>
            );
          })}
        </div>
        <style>{`@keyframes spin { from { transform: rotate(0); } to { transform: rotate(360deg); } }`}</style>
      </div>

      <div style={{
        marginTop: 'auto', textAlign: 'center',
        fontFamily: 'var(--font-mono)', fontSize: 10,
        color: 'var(--w-muted-2)', letterSpacing: '0.1em',
        textTransform: 'uppercase',
      }}>
        GPT-5.5 · web search enabled
      </div>
      </div>
    </div>
  );
}

// ═════════════════════════════════════════════════════════════
// RESULT — verdict card, summary, price band, drill-in sections
// ═════════════════════════════════════════════════════════════
function ResultScreen({ product, onBack, onOpen, onNew }) {
  const v = window.WORTHLY.verdicts[product.verdict];
  return (
    <div style={{ background: 'var(--w-cream)' }}>
      {/* Sticky-ish header */}
      <div style={{
        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
        padding: '60px 18px 14px',
      }}>
        <button onClick={onBack} style={iconBtn()}><Icon.ChevronLeft s={20} /></button>
        <span style={{
          fontFamily: 'var(--font-mono)', fontSize: 10,
          color: 'var(--w-muted)', letterSpacing: '0.12em', textTransform: 'uppercase',
        }}>Analysis · {new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</span>
        <button style={iconBtn()}>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
            <path d="M12 3v12M7 8l5-5 5 5M5 21h14" />
          </svg>
        </button>
      </div>

      {/* Hero verdict */}
      <div style={{ padding: '8px 18px 20px' }}>
        <div style={{
          background: 'var(--w-paper)',
          border: '0.5px solid var(--w-line)',
          borderRadius: 18, padding: 18,
          position: 'relative', overflow: 'hidden',
        }}>
          {/* Big traffic-light dot accent */}
          <div style={{
            position: 'absolute', top: -40, right: -40,
            width: 160, height: 160, borderRadius: '50%',
            background: v.soft, opacity: 0.85,
          }} />
          <div style={{ position: 'relative' }}>
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: 14, marginBottom: 18 }}>
              <ProductImage product={product} size={72} radius={12} />
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{
                  fontFamily: 'var(--font-mono)', fontSize: 10, fontWeight: 500,
                  letterSpacing: '0.12em', textTransform: 'uppercase',
                  color: 'var(--w-muted)', marginBottom: 4,
                }}>{product.category}</div>
                <div style={{
                  fontSize: 17, fontWeight: 600, lineHeight: 1.2,
                  color: 'var(--w-ink)', marginBottom: 6,
                }}>{product.name}</div>
                <div style={{ display: 'flex', alignItems: 'baseline', gap: 6 }}>
                  <Num size={13} color="var(--w-muted)" weight={400}>$</Num>
                  <Num size={18} weight={600}>{product.estimated_price.low}</Num>
                  <Num size={12} color="var(--w-muted-2)" weight={400}>—</Num>
                  <Num size={18} weight={600}>{product.estimated_price.high}</Num>
                </div>
              </div>
            </div>

            {/* Verdict block */}
            <div style={{
              display: 'flex', alignItems: 'center', gap: 14,
              padding: '14px 0', borderTop: '0.5px solid var(--w-line)',
              borderBottom: '0.5px solid var(--w-line)',
              marginBottom: 14,
            }}>
              <div style={{
                width: 56, height: 56, borderRadius: '50%',
                background: v.color,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                color: '#FAF8F2',
                boxShadow: `0 0 0 6px ${v.soft}`,
              }}>
                <span style={{
                  fontFamily: 'var(--font-mono)', fontSize: 11, fontWeight: 700,
                  letterSpacing: '0.08em',
                }}>{v.code}</span>
              </div>
              <div style={{ flex: 1 }}>
                <div style={{
                  fontFamily: 'var(--font-mono)', fontSize: 10, fontWeight: 500,
                  letterSpacing: '0.12em', textTransform: 'uppercase',
                  color: v.color, marginBottom: 4,
                }}>Verdict</div>
                <div style={{
                  fontFamily: 'var(--font-display)', fontStyle: 'italic',
                  fontSize: 24, lineHeight: 1.05, color: 'var(--w-ink)',
                }}>{product.advisor_tldr}</div>
              </div>
            </div>

            {/* Advisor summary */}
            <p style={{
              fontSize: 14, lineHeight: 1.55, color: 'var(--w-ink-2)',
              margin: 0,
            }}>{product.summary}</p>
          </div>
        </div>
      </div>

      {/* Price band card */}
      <div style={{ padding: '0 18px 14px' }}>
        <Card padding={16}>
          <div style={{
            display: 'flex', justifyContent: 'space-between',
            alignItems: 'baseline', marginBottom: 12,
          }}>
            <SectionLabel>Price right now</SectionLabel>
            <div style={{ display: 'flex', alignItems: 'baseline', gap: 6 }}>
              <Num size={18} weight={600}>${product.current_offer.price}</Num>
              <span style={{
                fontFamily: 'var(--font-mono)', fontSize: 11, fontWeight: 500,
                color: product.current_offer.delta_pct < 0 ? 'var(--w-buy)' : 'var(--w-muted)',
              }}>
                {product.current_offer.delta_pct > 0 ? '+' : ''}{product.current_offer.delta_pct}%
              </span>
            </div>
          </div>
          <PriceBand
            range={product.estimated_price}
            current={product.current_offer.price}
            fair={product.fair_price}
          />
          <div style={{
            display: 'flex', justifyContent: 'space-between', marginTop: 6,
            fontSize: 12, color: 'var(--w-muted)',
          }}>
            <span>at {product.current_offer.retailer}</span>
            <span style={{
              fontFamily: 'var(--font-mono)', fontSize: 10, letterSpacing: '0.06em',
              color: 'var(--w-buy)',
            }}>FAIR ZONE</span>
          </div>
        </Card>
      </div>

      {/* Why */}
      <div style={{ padding: '8px 18px 14px' }}>
        <Card padding={16}>
          <SectionLabel style={{ marginBottom: 12 }}>Why</SectionLabel>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
            {product.reasons_for?.map((r, i) => (
              <div key={i} style={{ display: 'flex', gap: 10, alignItems: 'flex-start' }}>
                <div style={{
                  width: 16, height: 16, borderRadius: '50%',
                  background: 'var(--w-buy-soft)', color: 'var(--w-buy)',
                  display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
                  marginTop: 2,
                }}>
                  <Icon.Check s={10} />
                </div>
                <span style={{ fontSize: 13, lineHeight: 1.5, color: 'var(--w-ink-2)' }}>{r}</span>
              </div>
            ))}
            {product.reasons_against?.map((r, i) => (
              <div key={i} style={{ display: 'flex', gap: 10, alignItems: 'flex-start' }}>
                <div style={{
                  width: 16, height: 16, borderRadius: '50%',
                  background: 'var(--w-wait-soft)', color: 'var(--w-wait)',
                  display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
                  marginTop: 2, fontSize: 11, fontWeight: 600,
                }}>!</div>
                <span style={{ fontSize: 13, lineHeight: 1.5, color: 'var(--w-ink-2)' }}>{r}</span>
              </div>
            ))}
          </div>
        </Card>
      </div>

      {/* Section drill-ins */}
      <div style={{ padding: '8px 18px 18px' }}>
        <Card padding={0} style={{ overflow: 'hidden' }}>
          <DrillRow
            label="Similar products"
            value={`${product.similar?.length || 0} found`}
            onClick={() => onOpen('similar')}
          />
          <Hairline />
          <DrillRow
            label="Reviews & reputation"
            value={`${product.reviews?.rating.toFixed(1)} · ${(product.reviews?.count / 1000).toFixed(1)}k reviews`}
            onClick={() => onOpen('reviews')}
          />
          <Hairline />
          <DrillRow
            label="Offers & price history"
            value={`${product.offers?.length || 0} retailers`}
            onClick={() => onOpen('offers')}
          />
        </Card>
      </div>

      {/* Bottom CTAs */}
      <div style={{ padding: '0 18px 24px', display: 'flex', gap: 8 }}>
        <PrimaryButton variant="paper" onClick={onNew} full style={{ height: 48 }}>
          <Icon.Plus s={16} /> New analysis
        </PrimaryButton>
        <PrimaryButton onClick={() => onOpen('offers')} full style={{ height: 48 }}>
          See best offer <Icon.ArrowRight s={16} />
        </PrimaryButton>
      </div>
    </div>
  );
}

function DrillRow({ label, value, onClick }) {
  return (
    <button onClick={onClick} style={{
      appearance: 'none', background: 'transparent', border: 0,
      width: '100%', textAlign: 'left', cursor: 'pointer',
      display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      padding: '16px 16px',
    }}>
      <span style={{ fontSize: 14, color: 'var(--w-ink)', fontWeight: 500 }}>{label}</span>
      <span style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
        <span style={{
          fontFamily: 'var(--font-mono)', fontSize: 11,
          color: 'var(--w-muted)', letterSpacing: '0.02em',
        }}>{value}</span>
        <Icon.ChevronRight s={14} c="var(--w-muted-2)" />
      </span>
    </button>
  );
}

// ═════════════════════════════════════════════════════════════
// SIMILAR PRODUCTS
// ═════════════════════════════════════════════════════════════
function SimilarScreen({ product, onBack }) {
  return (
    <div style={{ background: 'var(--w-cream)', minHeight: '100%' }}>
      <ScreenHeader onBack={onBack} eyebrow="Similar to" title={product.name} />
      <div style={{ padding: '4px 22px 14px' }}>
        <p style={{
          fontSize: 13, lineHeight: 1.5, color: 'var(--w-muted)', margin: '0 0 16px',
        }}>
          Three alternatives I'd consider — sorted by closest cost-benefit match.
        </p>
        <SectionLabel>Best value pick</SectionLabel>
      </div>
      <div style={{ padding: '0 18px 14px', display: 'flex', flexDirection: 'column', gap: 10 }}>
        {product.similar?.map((s, i) => (
          <Card key={s.id} padding={16}>
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: 12 }}>
              <ProductImage product={{ brand: s.name.split(' ')[0], image_tone: '#3A3835', image_accent: '#A8A2A0' }} size={60} radius={10} />
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{
                  display: 'flex', alignItems: 'center', gap: 6, marginBottom: 4,
                }}>
                  <span style={{
                    fontFamily: 'var(--font-mono)', fontSize: 10, fontWeight: 500,
                    letterSpacing: '0.1em', textTransform: 'uppercase',
                    color: 'var(--w-muted)',
                  }}>{s.relation}</span>
                  {i === 0 && (
                    <span style={{
                      padding: '2px 6px', background: 'var(--w-ink)', color: '#FAF8F2',
                      borderRadius: 4,
                      fontFamily: 'var(--font-mono)', fontSize: 9, fontWeight: 600,
                      letterSpacing: '0.08em',
                    }}>BEST VALUE</span>
                  )}
                </div>
                <div style={{ fontSize: 15, fontWeight: 600, color: 'var(--w-ink)', marginBottom: 6 }}>{s.name}</div>
                <div style={{ display: 'flex', alignItems: 'baseline', gap: 8, marginBottom: 8 }}>
                  <Num size={17} weight={600}>${s.price}</Num>
                  <span style={{
                    fontFamily: 'var(--font-mono)', fontSize: 11, color: 'var(--w-buy)',
                  }}>{s.delta}</span>
                  <span style={{ fontSize: 11, color: 'var(--w-muted-2)' }}>vs. {product.brand}</span>
                </div>
              </div>
              <div style={{
                textAlign: 'right', fontFamily: 'var(--font-mono)',
              }}>
                <div style={{
                  fontSize: 9, fontWeight: 500, letterSpacing: '0.1em',
                  color: 'var(--w-muted)', textTransform: 'uppercase',
                }}>Score</div>
                <Num size={20} weight={600}>{s.score}</Num>
              </div>
            </div>
            <p style={{
              fontSize: 13, lineHeight: 1.5, color: 'var(--w-ink-2)',
              margin: '10px 0 0', paddingTop: 12, borderTop: '0.5px solid var(--w-line)',
            }}>{s.note}</p>
          </Card>
        ))}
      </div>

      {/* Comparison table */}
      <div style={{ padding: '14px 22px 8px' }}>
        <SectionLabel>At a glance</SectionLabel>
      </div>
      <div style={{ padding: '0 18px 32px' }}>
        <Card padding={0} style={{ overflow: 'hidden' }}>
          <CompareTable product={product} />
        </Card>
      </div>
    </div>
  );
}

function CompareTable({ product }) {
  const rows = [
    { k: 'Price', f: (p) => `$${p.price ?? p.current_offer?.price ?? p.estimated_price?.mid}` },
    { k: 'Score', f: (p) => p.score ?? '—' },
    { k: 'Verdict', f: (p) => p.verdict ? window.WORTHLY.verdicts[p.verdict]?.label : '—' },
  ];
  const items = [{ ...product, label: 'This' }, ...product.similar.map(s => ({ ...s, label: s.name.split(' ')[0] }))];
  return (
    <div>
      {/* header row */}
      <div style={{ display: 'grid', gridTemplateColumns: '80px repeat(4, 1fr)', padding: '10px 14px' }}>
        <span style={{
          fontFamily: 'var(--font-mono)', fontSize: 9, color: 'var(--w-muted-2)',
          letterSpacing: '0.1em', textTransform: 'uppercase',
        }}></span>
        {items.slice(0, 4).map((it, i) => (
          <span key={i} style={{
            fontFamily: 'var(--font-mono)', fontSize: 9, fontWeight: 500,
            color: i === 0 ? 'var(--w-ink)' : 'var(--w-muted)',
            letterSpacing: '0.06em', textTransform: 'uppercase',
            textAlign: 'center',
          }}>{it.label}</span>
        ))}
      </div>
      <Hairline />
      {rows.map((r, i) => (
        <div key={r.k} style={{
          display: 'grid', gridTemplateColumns: '80px repeat(4, 1fr)',
          padding: '12px 14px',
          borderBottom: i < rows.length - 1 ? '0.5px solid var(--w-line)' : 0,
        }}>
          <span style={{ fontSize: 12, color: 'var(--w-muted)' }}>{r.k}</span>
          {items.slice(0, 4).map((it, j) => (
            <span key={j} style={{
              fontFamily: 'var(--font-mono)', fontSize: 13, fontWeight: 500,
              color: 'var(--w-ink)', textAlign: 'center',
              fontVariantNumeric: 'tabular-nums',
            }}>{r.f(it)}</span>
          ))}
        </div>
      ))}
    </div>
  );
}

// ═════════════════════════════════════════════════════════════
// REVIEWS
// ═════════════════════════════════════════════════════════════
function ReviewsScreen({ product, onBack }) {
  const r = product.reviews;
  return (
    <div style={{ background: 'var(--w-cream)', minHeight: '100%' }}>
      <ScreenHeader onBack={onBack} eyebrow="Reviews & reputation" title={product.name} />

      {/* Big rating card */}
      <div style={{ padding: '4px 18px 14px' }}>
        <Card padding={18}>
          <div style={{ display: 'flex', alignItems: 'flex-start', gap: 20 }}>
            <div>
              <Num size={56} weight={500} style={{ lineHeight: 1, display: 'block' }}>{r.rating}</Num>
              <div style={{ display: 'flex', gap: 2, marginTop: 6 }}>
                {[1,2,3,4,5].map(i => {
                  const fill = Math.min(1, Math.max(0, r.rating - (i - 1)));
                  return (
                    <svg key={i} width="14" height="14" viewBox="0 0 24 24">
                      <defs>
                        <linearGradient id={`star-${i}`}>
                          <stop offset={`${fill * 100}%`} stopColor="var(--w-ink)" />
                          <stop offset={`${fill * 100}%`} stopColor="var(--w-line-2)" />
                        </linearGradient>
                      </defs>
                      <path d="M12 2l3 7 7 .8-5.2 4.8L18.4 22 12 18l-6.4 4 1.6-7.4L2 9.8 9 9z"
                        fill={`url(#star-${i})`} />
                    </svg>
                  );
                })}
              </div>
              <div style={{
                fontFamily: 'var(--font-mono)', fontSize: 11,
                color: 'var(--w-muted)', marginTop: 6, letterSpacing: '0.04em',
              }}>{r.count.toLocaleString()} reviews</div>
            </div>
            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 6 }}>
              <BreakdownBar label="Positive" value={r.breakdown.pros} color="var(--w-buy)" />
              <BreakdownBar label="Mixed" value={r.breakdown.mixed} color="var(--w-wait)" />
              <BreakdownBar label="Negative" value={r.breakdown.cons} color="var(--w-skip)" />
            </div>
          </div>
        </Card>
      </div>

      {/* Pros */}
      <div style={{ padding: '8px 22px 6px' }}>
        <SectionLabel right={<span style={{ fontFamily: 'var(--font-mono)', fontSize: 10, color: 'var(--w-buy)' }}>What people love</span>}>Top pros</SectionLabel>
      </div>
      <div style={{ padding: '0 18px 14px', display: 'flex', flexDirection: 'column', gap: 8 }}>
        {r.top_pros.map(p => (
          <ReviewTagCard key={p.tag} tag={p.tag} count={p.count} quote={p.quote} sentiment="pro" />
        ))}
      </div>

      {/* Cons */}
      <div style={{ padding: '8px 22px 6px' }}>
        <SectionLabel right={<span style={{ fontFamily: 'var(--font-mono)', fontSize: 10, color: 'var(--w-skip)' }}>Common complaints</span>}>Top cons</SectionLabel>
      </div>
      <div style={{ padding: '0 18px 14px', display: 'flex', flexDirection: 'column', gap: 8 }}>
        {r.top_cons.map(p => (
          <ReviewTagCard key={p.tag} tag={p.tag} count={p.count} quote={p.quote} sentiment="con" />
        ))}
      </div>

      {/* Sources */}
      <div style={{ padding: '8px 22px 6px' }}>
        <SectionLabel>Sources</SectionLabel>
      </div>
      <div style={{ padding: '0 18px 32px' }}>
        <Card padding={14}>
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
            {r.sources.map(s => (
              <span key={s} style={{
                padding: '5px 10px',
                background: 'var(--w-cream-2)', borderRadius: 999,
                fontSize: 12, color: 'var(--w-ink-2)',
              }}>{s}</span>
            ))}
          </div>
        </Card>
      </div>
    </div>
  );
}

function BreakdownBar({ label, value, color }) {
  return (
    <div>
      <div style={{
        display: 'flex', justifyContent: 'space-between',
        fontSize: 11, color: 'var(--w-muted)', marginBottom: 3,
      }}>
        <span>{label}</span>
        <Num size={11} weight={500} color="var(--w-ink)">{value}%</Num>
      </div>
      <div style={{ height: 4, background: 'var(--w-line)', borderRadius: 2, overflow: 'hidden' }}>
        <div style={{ width: `${value}%`, height: '100%', background: color, borderRadius: 2 }} />
      </div>
    </div>
  );
}

function ReviewTagCard({ tag, count, quote, sentiment }) {
  const c = sentiment === 'pro' ? 'var(--w-buy)' : 'var(--w-skip)';
  const soft = sentiment === 'pro' ? 'var(--w-buy-soft)' : 'var(--w-skip-soft)';
  return (
    <Card padding={14}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 8 }}>
        <span style={{
          padding: '3px 8px', background: soft, color: c,
          borderRadius: 6,
          fontFamily: 'var(--font-mono)', fontSize: 11, fontWeight: 500,
        }}>{tag}</span>
        <Num size={11} color="var(--w-muted)" weight={500}>{count.toLocaleString()} mentions</Num>
      </div>
      <p style={{
        fontFamily: 'var(--font-display)', fontStyle: 'italic',
        fontSize: 15, lineHeight: 1.4, color: 'var(--w-ink-2)',
        margin: 0,
      }}>"{quote}"</p>
    </Card>
  );
}

// ═════════════════════════════════════════════════════════════
// OFFERS
// ═════════════════════════════════════════════════════════════
function OffersScreen({ product, onBack }) {
  const lowest = Math.min(...product.offers.map(o => o.price));
  return (
    <div style={{ background: 'var(--w-cream)', minHeight: '100%' }}>
      <ScreenHeader onBack={onBack} eyebrow="Offers & price" title={product.name} />

      {/* Best price callout */}
      <div style={{ padding: '4px 18px 14px' }}>
        <Card padding={18} style={{ background: 'var(--w-ink)', color: '#FAF8F2', border: 0 }}>
          <div style={{
            fontFamily: 'var(--font-mono)', fontSize: 10, fontWeight: 500,
            letterSpacing: '0.14em', textTransform: 'uppercase',
            color: 'rgba(250,248,242,0.5)', marginBottom: 8,
          }}>Best price right now</div>
          <div style={{ display: 'flex', alignItems: 'baseline', gap: 6, marginBottom: 4 }}>
            <Num size={42} weight={500} color="#FAF8F2">${lowest}</Num>
            <span style={{
              fontFamily: 'var(--font-mono)', fontSize: 12,
              color: 'rgba(250,248,242,0.6)', marginLeft: 6,
            }}>at {product.offers.find(o => o.price === lowest).retailer}</span>
          </div>
          <div style={{ fontSize: 13, color: 'rgba(250,248,242,0.7)', lineHeight: 1.4 }}>
            ${product.estimated_price.mid - lowest} below average · ${product.estimated_price.high - lowest} below MSRP
          </div>
        </Card>
      </div>

      {/* Price history sparkline */}
      <div style={{ padding: '8px 22px 6px' }}>
        <SectionLabel right={<Num size={10} color="var(--w-muted)" weight={500}>last 8 mo</Num>}>Price history</SectionLabel>
      </div>
      <div style={{ padding: '0 18px 14px' }}>
        <Card padding={14}>
          <Sparkline data={product.price_history} width={326} height={90} current={lowest} />
        </Card>
      </div>

      {/* All offers */}
      <div style={{ padding: '8px 22px 6px' }}>
        <SectionLabel>All retailers ({product.offers.length})</SectionLabel>
      </div>
      <div style={{ padding: '0 18px 32px', display: 'flex', flexDirection: 'column', gap: 6 }}>
        {product.offers.map((o, i) => (
          <Card key={o.retailer} padding={14}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
              <div style={{
                width: 36, height: 36, borderRadius: 8,
                background: 'var(--w-cream-2)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                fontFamily: 'var(--font-mono)', fontSize: 13, fontWeight: 600,
                color: 'var(--w-ink)', flexShrink: 0,
              }}>{o.retailer[0]}</div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 2 }}>
                  <span style={{ fontSize: 14, fontWeight: 500, color: 'var(--w-ink)' }}>{o.retailer}</span>
                  {o.badge && (
                    <span style={{
                      padding: '2px 6px',
                      background: o.badge === 'Best price' ? 'var(--w-ink)' : 'var(--w-cream-2)',
                      color: o.badge === 'Best price' ? '#FAF8F2' : 'var(--w-muted)',
                      borderRadius: 4,
                      fontFamily: 'var(--font-mono)', fontSize: 9, fontWeight: 600,
                      letterSpacing: '0.06em', textTransform: 'uppercase',
                    }}>{o.badge}</span>
                  )}
                </div>
                <div style={{ fontSize: 11, color: 'var(--w-muted)' }}>
                  {o.shipping === 'Free' ? 'Free shipping' : `Shipping ${o.shipping}`} · {o.in_stock ? 'In stock' : 'Out of stock'}
                </div>
              </div>
              <div style={{ textAlign: 'right' }}>
                <Num size={17} weight={600}>${o.price}</Num>
                {o.in_stock && (
                  <div style={{
                    fontFamily: 'var(--font-mono)', fontSize: 10,
                    color: o.price === lowest ? 'var(--w-buy)' : 'var(--w-muted)',
                    marginTop: 2,
                  }}>
                    {o.price === lowest ? 'LOWEST' : `+$${o.price - lowest}`}
                  </div>
                )}
              </div>
            </div>
          </Card>
        ))}
      </div>
    </div>
  );
}

// ═════════════════════════════════════════════════════════════
// HISTORY TAB
// ═════════════════════════════════════════════════════════════
function HistoryScreen({ onOpen }) {
  const [filter, setFilter] = useState('all');
  const items = window.WORTHLY.history.filter(h => filter === 'all' || h.verdict === filter);

  // Group by day
  const groups = [];
  items.forEach(it => {
    const day = it.relative.includes('Just') ? 'Today'
      : it.relative === '1d' ? 'Yesterday'
      : it.relative.includes('d') ? 'This week'
      : 'Earlier';
    const last = groups[groups.length - 1];
    if (last && last.day === day) last.items.push(it);
    else groups.push({ day, items: [it] });
  });

  return (
    <div style={{ padding: '60px 0 16px' }}>
      <div style={{ padding: '0 22px 12px' }}>
        <div style={{
          fontFamily: 'var(--font-mono)', fontSize: 11,
          letterSpacing: '0.14em', textTransform: 'uppercase',
          color: 'var(--w-muted)', marginBottom: 10,
        }}>History</div>
        <h1 style={{
          fontFamily: 'var(--font-display)', fontWeight: 400,
          fontSize: 36, lineHeight: 1, letterSpacing: '-0.01em',
          color: 'var(--w-ink)', margin: 0,
        }}>Your verdicts.</h1>
      </div>

      {/* Filter tabs */}
      <div style={{
        display: 'flex', gap: 6, padding: '14px 22px 6px',
        overflowX: 'auto', scrollbarWidth: 'none',
      }}>
        {[
          { k: 'all', label: 'All', count: 5 },
          { k: 'buy', label: 'Buy', count: 2 },
          { k: 'wait', label: 'Wait', count: 2 },
          { k: 'skip', label: 'Skip', count: 1 },
        ].map(f => (
          <button key={f.k} onClick={() => setFilter(f.k)} style={{
            appearance: 'none', cursor: 'pointer',
            background: filter === f.k ? 'var(--w-ink)' : 'transparent',
            color: filter === f.k ? '#FAF8F2' : 'var(--w-ink)',
            border: filter === f.k ? '1px solid var(--w-ink)' : '0.5px solid var(--w-line-2)',
            borderRadius: 999, padding: '6px 12px',
            fontFamily: 'var(--font-ui)', fontSize: 12, fontWeight: 500,
            display: 'flex', alignItems: 'center', gap: 6,
            whiteSpace: 'nowrap', flexShrink: 0,
          }}>
            {f.label}
            <span style={{
              fontFamily: 'var(--font-mono)', fontSize: 10,
              color: filter === f.k ? 'rgba(250,248,242,0.6)' : 'var(--w-muted-2)',
            }}>{f.count}</span>
          </button>
        ))}
      </div>

      {/* Groups */}
      <div style={{ padding: '12px 0 0' }}>
        {groups.map(g => (
          <div key={g.day}>
            <div style={{ padding: '12px 22px 6px' }}>
              <SectionLabel>{g.day}</SectionLabel>
            </div>
            <div style={{ padding: '0 18px', display: 'flex', flexDirection: 'column', gap: 8 }}>
              {g.items.map(it => (
                <HistoryRow key={it.id} item={it} onClick={() => onOpen(it)} />
              ))}
            </div>
          </div>
        ))}
        {items.length === 0 && (
          <div style={{ padding: '40px 22px', textAlign: 'center', color: 'var(--w-muted)' }}>
            Nothing here yet.
          </div>
        )}
      </div>
    </div>
  );
}

// ═════════════════════════════════════════════════════════════
// PROFILE TAB
// ═════════════════════════════════════════════════════════════
function ProfileScreen({ onSignOut }) {
  return (
    <div style={{ padding: '60px 0 16px' }}>
      <div style={{ padding: '0 22px 24px' }}>
        <div style={{
          fontFamily: 'var(--font-mono)', fontSize: 11,
          letterSpacing: '0.14em', textTransform: 'uppercase',
          color: 'var(--w-muted)', marginBottom: 10,
        }}>You</div>
        <h1 style={{
          fontFamily: 'var(--font-display)', fontWeight: 400,
          fontSize: 36, lineHeight: 1, letterSpacing: '-0.01em',
          color: 'var(--w-ink)', margin: 0,
        }}>Profile</h1>
      </div>

      <div style={{ padding: '0 18px 18px' }}>
        <Card padding={18}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
            <div style={{
              width: 56, height: 56, borderRadius: '50%',
              background: 'var(--w-ink)', color: '#FAF8F2',
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              fontFamily: 'var(--font-display)', fontStyle: 'italic', fontSize: 24,
            }}>Y</div>
            <div style={{ flex: 1 }}>
              <div style={{ fontSize: 16, fontWeight: 500, color: 'var(--w-ink)' }}>Yan Stein</div>
              <div style={{ fontSize: 13, color: 'var(--w-muted)' }}>yan@worthly.app</div>
            </div>
            <button style={iconBtn()}><Icon.ChevronRight s={14} /></button>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 0, marginTop: 16, paddingTop: 14, borderTop: '0.5px solid var(--w-line)' }}>
            <Stat label="Analyses" value="32" />
            <Stat label="Saved" value="7" />
            <Stat label="$ saved" value="$420" />
          </div>
        </Card>
      </div>

      <div style={{ padding: '0 22px 8px' }}>
        <SectionLabel>Plan</SectionLabel>
      </div>
      <div style={{ padding: '0 18px 18px' }}>
        <Card padding={16} style={{ background: 'linear-gradient(135deg, var(--w-cream-2) 0%, var(--w-paper) 100%)' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 10 }}>
            <div>
              <div style={{
                fontFamily: 'var(--font-mono)', fontSize: 10, fontWeight: 500,
                letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--w-muted)',
              }}>Current plan</div>
              <div style={{
                fontFamily: 'var(--font-display)', fontStyle: 'italic',
                fontSize: 24, color: 'var(--w-ink)', marginTop: 2,
              }}>Free</div>
            </div>
            <Num size={28} weight={500}>32<span style={{ fontSize: 14, color: 'var(--w-muted-2)' }}>/50</span></Num>
          </div>
          <div style={{ height: 4, background: 'var(--w-line)', borderRadius: 2, overflow: 'hidden', marginBottom: 14 }}>
            <div style={{ width: '64%', height: '100%', background: 'var(--w-ink)' }} />
          </div>
          <PrimaryButton style={{ height: 44 }}>
            <Icon.Bolt s={14} c="#FAF8F2" /> Upgrade to Pro
          </PrimaryButton>
        </Card>
      </div>

      <div style={{ padding: '0 22px 8px' }}>
        <SectionLabel>Settings</SectionLabel>
      </div>
      <div style={{ padding: '0 18px 32px' }}>
        <Card padding={0} style={{ overflow: 'hidden' }}>
          {[
            { l: 'Saved products', v: '7' },
            { l: 'Notifications', v: 'On' },
            { l: 'Currency', v: 'USD' },
            { l: 'Region', v: 'United States' },
            { l: 'About Worthly', v: 'v1.0.2' },
          ].map((r, i, arr) => (
            <React.Fragment key={r.l}>
              <DrillRow label={r.l} value={r.v} onClick={() => {}} />
              {i < arr.length - 1 && <Hairline />}
            </React.Fragment>
          ))}
        </Card>
        <button onClick={onSignOut} style={{
          appearance: 'none', background: 'transparent', border: 0,
          width: '100%', marginTop: 18, padding: 14,
          fontFamily: 'var(--font-ui)', fontSize: 13, color: 'var(--w-skip)',
          cursor: 'pointer',
        }}>Sign out</button>
      </div>
    </div>
  );
}

function Stat({ label, value }) {
  return (
    <div style={{ textAlign: 'center' }}>
      <Num size={20} weight={600} style={{ display: 'block' }}>{value}</Num>
      <span style={{
        fontFamily: 'var(--font-mono)', fontSize: 10,
        color: 'var(--w-muted)', letterSpacing: '0.08em', textTransform: 'uppercase',
      }}>{label}</span>
    </div>
  );
}

Object.assign(window, {
  OnboardingScreen, LoginScreen, HomeScreen, AnalyzingScreen,
  ResultScreen, SimilarScreen, ReviewsScreen, OffersScreen,
  HistoryScreen, ProfileScreen,
});

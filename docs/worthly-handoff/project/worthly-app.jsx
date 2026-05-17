/* worthly-app.jsx — Root app shell. Handles navigation between screens, the
   bottom tab bar, the staged iOS frame, and Tweaks. */

const { useState, useEffect, useMemo } = React;

const TWEAK_DEFAULTS = /*EDITMODE-BEGIN*/{
  "accent": "#1B7A3F",
  "density": "regular",
  "verdictStyle": "card",
  "dark": false,
  "showStageNav": true,
  "advisorTone": "friendly"
}/*EDITMODE-END*/;

// All the named "screens" the user can jump to from the stage-side nav.
const STAGE_LINKS = [
  { k: 'onboarding', n: '01', label: 'Onboarding' },
  { k: 'login',      n: '02', label: 'Sign in' },
  { k: 'home',       n: '03', label: 'Home' },
  { k: 'analyzing',  n: '04', label: 'Analyzing…' },
  { k: 'result',     n: '05', label: 'Result' },
  { k: 'similar',    n: '06', label: 'Similar products' },
  { k: 'reviews',    n: '07', label: 'Reviews' },
  { k: 'offers',     n: '08', label: 'Offers' },
  { k: 'history',    n: '09', label: 'History' },
  { k: 'profile',    n: '10', label: 'Profile' },
];

function App() {
  const [t, setTweak] = useTweaks(TWEAK_DEFAULTS);

  // Navigation state ────────────────────────────────────────────
  // route: 'onboarding' | 'login' | 'app'
  // tab (when route='app'): 'home' | 'history' | 'profile'
  // stack: per-tab modal stack of analysis screens
  const [route, setRoute] = useState('onboarding');
  const [tab, setTab] = useState('home');
  const [stack, setStack] = useState([]);  // [{screen: 'analyzing'|'result'|..., product, query, kind}]
  const [query, setQuery] = useState('Is the Logitech MX Master 3S worth buying?');

  // Stage-side jump (deterministic — sets state to land on the named screen)
  const jumpTo = (k) => {
    if (k === 'onboarding') { setRoute('onboarding'); return; }
    if (k === 'login') { setRoute('login'); return; }
    if (k === 'home') { setRoute('app'); setTab('home'); setStack([]); return; }
    if (k === 'history') { setRoute('app'); setTab('history'); setStack([]); return; }
    if (k === 'profile') { setRoute('app'); setTab('profile'); setStack([]); return; }
    if (k === 'analyzing') {
      setRoute('app'); setTab('home');
      setStack([{ screen: 'analyzing', kind: 'text', query, productId: 'mx-master-3s' }]);
      return;
    }
    if (k === 'result' || k === 'similar' || k === 'reviews' || k === 'offers') {
      setRoute('app'); setTab('home');
      const pid = 'mx-master-3s';
      const layers = [{ screen: 'result', productId: pid }];
      if (k !== 'result') layers.push({ screen: k, productId: pid });
      setStack(layers);
      return;
    }
  };

  // Active stage link for highlight
  const activeStage = useMemo(() => {
    if (route === 'onboarding') return 'onboarding';
    if (route === 'login') return 'login';
    if (stack.length) return stack[stack.length - 1].screen;
    return tab;
  }, [route, tab, stack]);

  // Action handlers ─────────────────────────────────────────────
  const startAnalysis = ({ kind, query: q }) => {
    // For the prototype every analysis lands on MX Master 3S
    setStack([{ screen: 'analyzing', kind, query: q, productId: 'mx-master-3s' }]);
  };
  const onAnalysisDone = () => {
    setStack(s => {
      const last = s[s.length - 1];
      return [{ screen: 'result', productId: last.productId }];
    });
  };
  const openSection = (section) => {
    setStack(s => [...s, { screen: section, productId: s[s.length - 1].productId }]);
  };
  const popStack = () => setStack(s => s.slice(0, -1));
  const openHistoryItem = (h) => {
    const pid = window.WORTHLY.products[h.product_id] ? h.product_id : 'mx-master-3s';
    setRoute('app'); setTab('home');
    setStack([{ screen: 'result', productId: pid }]);
  };

  // Screen renderer ─────────────────────────────────────────────
  const renderScreen = () => {
    if (route === 'onboarding') return <OnboardingScreen onDone={() => setRoute('login')} />;
    if (route === 'login') return <LoginScreen onLogin={() => { setRoute('app'); setTab('home'); }} onBack={() => setRoute('onboarding')} />;

    // route === 'app'
    if (stack.length > 0) {
      const top = stack[stack.length - 1];
      const product = window.WORTHLY.products[top.productId];
      if (top.screen === 'analyzing') {
        return <AnalyzingScreen query={top.query} kind={top.kind} onDone={onAnalysisDone} />;
      }
      if (top.screen === 'result') {
        return <ResultScreen
          product={product}
          onBack={() => { setStack([]); setTab('home'); }}
          onOpen={openSection}
          onNew={() => { setStack([]); setTab('home'); }}
        />;
      }
      if (top.screen === 'similar') {
        return <SimilarScreen product={product} onBack={popStack} />;
      }
      if (top.screen === 'reviews') {
        return <ReviewsScreen product={product} onBack={popStack} />;
      }
      if (top.screen === 'offers') {
        return <OffersScreen product={product} onBack={popStack} />;
      }
    }

    if (tab === 'home') return <HomeScreen
      onAnalyze={startAnalysis}
      onOpenHistoryItem={openHistoryItem}
      query={query} setQuery={setQuery}
    />;
    if (tab === 'history') return <HistoryScreen onOpen={openHistoryItem} />;
    if (tab === 'profile') return <ProfileScreen onSignOut={() => setRoute('login')} />;
    return null;
  };

  // Tab bar visible only inside the app, not in modal stacks
  const showTabBar = route === 'app' && stack.length === 0;

  // Apply tweak: accent CSS var (also drives buy color)
  useEffect(() => {
    document.documentElement.style.setProperty('--w-buy', t.accent);
  }, [t.accent]);

  return (
    <div className="stage">
      {/* Left: project intro + screen jumps */}
      {t.showStageNav && (
        <div className="stage-side">
          <div className="stage-eyebrow">A Worthly prototype</div>
          <h1 className="stage-mark">Worthly.</h1>
          <p className="stage-desc">
            An iOS app that turns a product photo or text into a clear buy / wait / skip verdict —
            built on top of the Worthly authenticated API.
          </p>
          <div className="stage-nav">
            {STAGE_LINKS.map(l => (
              <button key={l.k}
                className={activeStage === l.k ? 'active' : ''}
                onClick={() => jumpTo(l.k)}>
                <span>{l.label}</span>
                <span className="num">{l.n}</span>
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Center: phone */}
      <div style={{ position: 'relative' }}>
        <IOSDevice width={402} height={874}>
          <div key={`${route}-${tab}-${stack.length}-${stack[stack.length - 1]?.screen || ''}`}
               style={{ height: '100%', overflowY: 'auto', position: 'relative', background: 'var(--w-cream)' }}>
            {renderScreen()}
            {showTabBar && <div style={{ height: 92 }} />}
          </div>
          {showTabBar && (
            <div style={{
              position: 'absolute', left: 0, right: 0, bottom: 0,
              padding: '0 16px 22px 16px',
              zIndex: 40,
              pointerEvents: 'none',
            }}>
              <div style={{
                pointerEvents: 'auto',
                background: 'rgba(255,255,255,0.82)',
                backdropFilter: 'blur(20px) saturate(180%)',
                WebkitBackdropFilter: 'blur(20px) saturate(180%)',
                border: '0.5px solid var(--w-line-2)',
                borderRadius: 22,
                padding: '8px 6px',
                display: 'flex', justifyContent: 'space-around',
                boxShadow: '0 8px 24px rgba(20,19,15,0.08)',
              }}>
                <TabButton k="home" tab={tab} setTab={setTab} icon={Icon.Home} label="Ask" />
                <TabButton k="history" tab={tab} setTab={setTab} icon={Icon.Clock} label="History" />
                <TabButton k="profile" tab={tab} setTab={setTab} icon={Icon.User} label="You" />
              </div>
            </div>
          )}
        </IOSDevice>
      </div>

      {/* Tweaks panel */}
      <TweaksPanel title="Tweaks">
        <TweakSection label="Theme">
          <TweakColor label="Verdict accent" value={t.accent}
            options={[
              '#1B7A3F',  // green (default)
              '#0F6BD0',  // confident blue
              '#A04A18',  // burnt orange
              '#5B3FB8',  // editorial purple
            ]}
            onChange={(v) => setTweak('accent', v)} />
        </TweakSection>

        <TweakSection label="Stage">
          <TweakToggle label="Show side nav" value={t.showStageNav}
            onChange={(v) => setTweak('showStageNav', v)} />
        </TweakSection>

        <TweakSection label="Jump to screen">
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 4 }}>
            {STAGE_LINKS.map(l => (
              <button key={l.k}
                onClick={() => jumpTo(l.k)}
                style={{
                  appearance: 'none',
                  background: activeStage === l.k ? 'rgba(0,0,0,0.78)' : 'rgba(0,0,0,0.06)',
                  color: activeStage === l.k ? '#fff' : 'inherit',
                  border: 0, borderRadius: 6,
                  padding: '6px 8px', cursor: 'pointer',
                  font: 'inherit', textAlign: 'left',
                  display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                }}>
                <span>{l.label}</span>
                <span style={{ fontFamily: 'var(--font-mono)', fontSize: 9, opacity: 0.5 }}>{l.n}</span>
              </button>
            ))}
          </div>
        </TweakSection>
      </TweaksPanel>
    </div>
  );
}

function TabButton({ k, tab, setTab, icon: Ic, label }) {
  const active = tab === k;
  return (
    <button onClick={() => setTab(k)} style={{
      appearance: 'none', background: 'transparent', border: 0, cursor: 'pointer',
      flex: 1, padding: '8px 4px 6px',
      display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 3,
      color: active ? 'var(--w-ink)' : 'var(--w-muted-2)',
    }}>
      <Ic s={22} filled={active} c="currentColor" />
      <span style={{
        fontFamily: 'var(--font-mono)', fontSize: 10, fontWeight: 500,
        letterSpacing: '0.08em', textTransform: 'uppercase',
      }}>{label}</span>
    </button>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);

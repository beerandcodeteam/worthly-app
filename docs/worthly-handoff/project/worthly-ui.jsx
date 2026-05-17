/* worthly-ui.jsx — Shared primitives: logo, icons, screen chrome, verdict pill,
   price bands, etc. All exported to window. */

// ─────────────────────────────────────────────────────────────
// Wordmark
// ─────────────────────────────────────────────────────────────
function WorthlyMark({ size = 24, color = "var(--w-ink)" }) {
  return (
    <span style={{
      fontFamily: 'var(--font-display)',
      fontStyle: 'italic',
      fontSize: size,
      lineHeight: 1,
      letterSpacing: '-0.01em',
      color,
      display: 'inline-flex',
      alignItems: 'baseline',
      gap: 1,
    }}>
      Worthly
      <span style={{
        width: 5, height: 5, borderRadius: '50%',
        background: 'var(--w-buy)', display: 'inline-block',
        marginBottom: size * 0.12,
      }} />
    </span>
  );
}

// ─────────────────────────────────────────────────────────────
// Icons — minimal stroked set
// ─────────────────────────────────────────────────────────────
const Icon = {
  Search: ({ s = 18, c = 'currentColor' }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill="none" stroke={c} strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
    </svg>
  ),
  Camera: ({ s = 18, c = 'currentColor' }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill="none" stroke={c} strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <path d="M3 8h3l2-3h8l2 3h3v11H3z" /><circle cx="12" cy="13" r="4" />
    </svg>
  ),
  Mic: ({ s = 18, c = 'currentColor' }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill="none" stroke={c} strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <rect x="9" y="3" width="6" height="12" rx="3" /><path d="M5 11a7 7 0 0 0 14 0M12 18v3" />
    </svg>
  ),
  Sparkle: ({ s = 18, c = 'currentColor' }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill="none" stroke={c} strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <path d="M12 3v6M12 15v6M3 12h6M15 12h6M5.6 5.6l4.2 4.2M14.2 14.2l4.2 4.2M5.6 18.4l4.2-4.2M14.2 9.8l4.2-4.2" />
    </svg>
  ),
  ArrowRight: ({ s = 18, c = 'currentColor' }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill="none" stroke={c} strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 12h14M13 5l7 7-7 7" />
    </svg>
  ),
  ChevronLeft: ({ s = 18, c = 'currentColor' }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill="none" stroke={c} strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M15 5l-7 7 7 7" />
    </svg>
  ),
  ChevronRight: ({ s = 14, c = 'currentColor' }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill="none" stroke={c} strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M9 5l7 7-7 7" />
    </svg>
  ),
  Close: ({ s = 18, c = 'currentColor' }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill="none" stroke={c} strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 5l14 14M19 5l-14 14" />
    </svg>
  ),
  Home: ({ s = 22, c = 'currentColor', filled = false }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill={filled ? c : 'none'} stroke={c} strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z" />
    </svg>
  ),
  Clock: ({ s = 22, c = 'currentColor', filled = false }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill={filled ? c : 'none'} stroke={c} strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="12" cy="12" r="9" fill={filled ? c : 'none'} />
      <path d="M12 7v5l3 2" stroke={filled ? 'var(--w-cream)' : c} />
    </svg>
  ),
  User: ({ s = 22, c = 'currentColor', filled = false }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill={filled ? c : 'none'} stroke={c} strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <circle cx="12" cy="8" r="4" /><path d="M4 21c1-4 4.5-6 8-6s7 2 8 6" />
    </svg>
  ),
  Check: ({ s = 14, c = 'currentColor' }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill="none" stroke={c} strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M4 12l5 5L20 6" />
    </svg>
  ),
  Plus: ({ s = 16, c = 'currentColor' }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill="none" stroke={c} strokeWidth="1.8" strokeLinecap="round">
      <path d="M12 5v14M5 12h14" />
    </svg>
  ),
  Bolt: ({ s = 14, c = 'currentColor' }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill={c} stroke="none">
      <path d="M13 2L4 14h6l-1 8 9-12h-6z" />
    </svg>
  ),
  Apple: ({ s = 18, c = 'currentColor' }) => (
    <svg width={s} height={s} viewBox="0 0 24 24" fill={c}>
      <path d="M17.5 12.5c0-2.4 2-3.5 2.1-3.6-1.1-1.6-2.9-1.9-3.5-1.9-1.5-.1-2.9.9-3.7.9-.8 0-1.9-.9-3.2-.8-1.6 0-3.1.9-4 2.4-1.7 2.9-.4 7.2 1.2 9.6.8 1.2 1.8 2.5 3.1 2.4 1.2 0 1.7-.8 3.2-.8s2 .8 3.3.8c1.4 0 2.2-1.2 3.1-2.4.6-.8 1.1-1.8 1.4-2.8-2.9-1.1-3-3.7-3-3.8zM15 4.8c.7-.8 1.1-1.9 1-3-1 0-2.2.7-2.9 1.5-.6.7-1.2 1.8-1 2.9 1.1.1 2.2-.6 2.9-1.4z"/>
    </svg>
  ),
  Google: ({ s = 18 }) => (
    <svg width={s} height={s} viewBox="0 0 24 24">
      <path fill="#4285F4" d="M22 12.2c0-.7-.1-1.4-.2-2H12v3.8h5.6c-.2 1.3-1 2.4-2 3.1v2.6h3.3c1.9-1.8 3-4.4 3-7.5z"/>
      <path fill="#34A853" d="M12 22c2.7 0 5-.9 6.7-2.4l-3.3-2.5c-.9.6-2 1-3.4 1-2.6 0-4.8-1.7-5.6-4.1H3v2.6C4.7 19.8 8.1 22 12 22z"/>
      <path fill="#FBBC05" d="M6.4 14C6.2 13.4 6 12.7 6 12s.1-1.4.4-2V7.4H3C2.3 8.8 2 10.4 2 12s.4 3.2 1 4.6L6.4 14z"/>
      <path fill="#EA4335" d="M12 5.9c1.5 0 2.8.5 3.8 1.5l2.9-2.9C16.9 2.9 14.7 2 12 2 8.1 2 4.7 4.2 3 7.4L6.4 10C7.2 7.6 9.4 5.9 12 5.9z"/>
    </svg>
  ),
};

// ─────────────────────────────────────────────────────────────
// Verdict — traffic-light pill + dot
// ─────────────────────────────────────────────────────────────
function VerdictPill({ verdict, size = 'md', style = {} }) {
  const v = window.WORTHLY.verdicts[verdict];
  if (!v) return null;
  const dims = size === 'lg'
    ? { fs: 13, py: 7, px: 12, gap: 8, dot: 8 }
    : size === 'sm'
    ? { fs: 10, py: 3, px: 7, gap: 5, dot: 5 }
    : { fs: 11, py: 4, px: 9, gap: 6, dot: 6 };
  return (
    <span style={{
      display: 'inline-flex', alignItems: 'center', gap: dims.gap,
      padding: `${dims.py}px ${dims.px}px`,
      background: v.soft,
      color: v.color,
      borderRadius: 999,
      fontFamily: 'var(--font-mono)',
      fontSize: dims.fs,
      fontWeight: 600,
      letterSpacing: '0.08em',
      textTransform: 'uppercase',
      ...style,
    }}>
      <span style={{
        width: dims.dot, height: dims.dot, borderRadius: '50%',
        background: v.dot,
      }} />
      {v.code}
    </span>
  );
}

// ─────────────────────────────────────────────────────────────
// Mono number with tabular-nums (for prices, scores, etc.)
// ─────────────────────────────────────────────────────────────
function Num({ children, size, weight = 500, color, style = {} }) {
  return (
    <span style={{
      fontFamily: 'var(--font-mono)',
      fontVariantNumeric: 'tabular-nums',
      fontWeight: weight,
      fontSize: size,
      color,
      letterSpacing: '-0.01em',
      ...style,
    }}>{children}</span>
  );
}

// ─────────────────────────────────────────────────────────────
// Section label — small uppercase mono caption
// ─────────────────────────────────────────────────────────────
function SectionLabel({ children, right, style = {} }) {
  return (
    <div style={{
      display: 'flex', alignItems: 'baseline', justifyContent: 'space-between',
      fontFamily: 'var(--font-mono)',
      fontSize: 10, fontWeight: 500,
      letterSpacing: '0.14em', textTransform: 'uppercase',
      color: 'var(--w-muted)',
      ...style,
    }}>
      <span>{children}</span>
      {right}
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// Hairline divider
// ─────────────────────────────────────────────────────────────
function Hairline({ style = {} }) {
  return <div style={{ height: 1, background: 'var(--w-line)', ...style }} />;
}

// ─────────────────────────────────────────────────────────────
// Screen header — back chevron + title + close
// ─────────────────────────────────────────────────────────────
function ScreenHeader({ title, eyebrow, onBack, onClose, right, sticky = false, transparent = false }) {
  return (
    <div style={{
      display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      padding: '62px 18px 14px',
      background: transparent ? 'transparent' : 'var(--w-cream)',
      position: sticky ? 'sticky' : 'relative',
      top: 0, zIndex: 10,
      gap: 12,
    }}>
      {onBack ? (
        <button type="button" onClick={onBack} style={iconBtn()}>
          <Icon.ChevronLeft s={20} />
        </button>
      ) : <span style={{ width: 32 }} />}
      <div style={{ flex: 1, textAlign: 'center', overflow: 'hidden' }}>
        {eyebrow && (
          <div style={{
            fontFamily: 'var(--font-mono)', fontSize: 10, fontWeight: 500,
            letterSpacing: '0.12em', textTransform: 'uppercase',
            color: 'var(--w-muted)',
          }}>{eyebrow}</div>
        )}
        {title && (
          <div style={{
            fontSize: 15, fontWeight: 500, color: 'var(--w-ink)',
            whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis',
          }}>{title}</div>
        )}
      </div>
      {right || (onClose ? (
        <button type="button" onClick={onClose} style={iconBtn()}>
          <Icon.Close s={18} />
        </button>
      ) : <span style={{ width: 32 }} />)}
    </div>
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

// ─────────────────────────────────────────────────────────────
// Product image — abstract gradient block w/ "image" annotation
// ─────────────────────────────────────────────────────────────
function ProductImage({ product, size = 80, radius = 12 }) {
  const tone = product?.image_tone || '#3A3835';
  const accent = product?.image_accent || '#C8B68A';
  return (
    <div style={{
      width: size, height: size, borderRadius: radius,
      background: `linear-gradient(140deg, ${tone} 0%, #1A1815 60%, ${tone} 100%)`,
      position: 'relative', overflow: 'hidden', flexShrink: 0,
      boxShadow: 'inset 0 0 0 0.5px rgba(255,255,255,0.05)',
    }}>
      {/* abstract product silhouette */}
      <div style={{
        position: 'absolute',
        left: '18%', top: '22%', width: '64%', height: '56%',
        background: `radial-gradient(ellipse 80% 70% at 40% 35%, ${accent}55 0%, transparent 60%), linear-gradient(180deg, ${accent}30 0%, transparent 80%)`,
        borderRadius: '46% 54% 50% 50% / 50% 40% 60% 50%',
        filter: 'blur(0.3px)',
      }} />
      <div style={{
        position: 'absolute',
        left: '24%', top: '40%', width: '52%', height: '28%',
        background: `${accent}aa`,
        borderRadius: '50% 50% 40% 40%',
        opacity: 0.55,
      }} />
      {/* corner brand initial */}
      <div style={{
        position: 'absolute', bottom: 4, right: 6,
        fontFamily: 'var(--font-mono)', fontSize: 8, fontWeight: 500,
        color: 'rgba(255,255,255,0.45)', letterSpacing: '0.1em',
      }}>
        {(product?.brand || '').slice(0, 4).toUpperCase()}
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// Primary button — ink block, mono caption inside
// ─────────────────────────────────────────────────────────────
function PrimaryButton({ children, onClick, disabled, full = true, variant = 'ink', style = {} }) {
  const bg = disabled ? 'rgba(20,19,15,0.4)' :
    variant === 'ink' ? 'var(--w-ink)' :
    variant === 'buy' ? 'var(--w-buy)' :
    variant === 'paper' ? 'var(--w-paper)' : 'var(--w-ink)';
  const color = variant === 'paper' ? 'var(--w-ink)' : '#FAF8F2';
  return (
    <button type="button" onClick={disabled ? undefined : onClick} disabled={disabled} style={{
      appearance: 'none', border: variant === 'paper' ? '1px solid var(--w-line-2)' : 0,
      background: bg, color,
      width: full ? '100%' : 'auto',
      height: 52, padding: '0 22px', borderRadius: 14,
      fontFamily: 'var(--font-ui)', fontSize: 15, fontWeight: 500,
      cursor: disabled ? 'default' : 'pointer',
      display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8,
      letterSpacing: '-0.005em',
      ...style,
    }}>{children}</button>
  );
}

// ─────────────────────────────────────────────────────────────
// Card — white surface with hairline + radius
// ─────────────────────────────────────────────────────────────
function Card({ children, padding = 16, style = {} }) {
  return (
    <div style={{
      background: 'var(--w-paper)',
      borderRadius: 14,
      border: '0.5px solid var(--w-line)',
      padding,
      ...style,
    }}>{children}</div>
  );
}

// ─────────────────────────────────────────────────────────────
// Price band — horizontal line with low / mid / current markers
// ─────────────────────────────────────────────────────────────
function PriceBand({ range, current, fair }) {
  // range: {low, mid, high}; current: number; fair: {low, fair, expensive}
  const min = range.low - 8;
  const max = range.high + 8;
  const pct = (v) => Math.max(0, Math.min(100, ((v - min) / (max - min)) * 100));
  const fairZone = fair ? { left: pct(fair.low), right: 100 - pct(fair.expensive) } : null;

  return (
    <div style={{ padding: '4px 0' }}>
      <div style={{ position: 'relative', height: 56 }}>
        {/* baseline */}
        <div style={{
          position: 'absolute', top: 28, left: 0, right: 0, height: 1,
          background: 'var(--w-line-2)',
        }} />
        {/* fair zone */}
        {fairZone && (
          <div style={{
            position: 'absolute', top: 22, height: 12,
            left: `${fairZone.left}%`, right: `${fairZone.right}%`,
            background: 'var(--w-buy-soft)',
            borderRadius: 2,
          }} />
        )}
        {/* tick marks */}
        {[range.low, range.mid, range.high].map((v, i) => (
          <div key={i} style={{
            position: 'absolute', top: 22, left: `${pct(v)}%`, transform: 'translateX(-50%)',
            width: 1, height: 12, background: 'var(--w-line-2)',
          }} />
        ))}
        {/* current marker */}
        <div style={{
          position: 'absolute', top: 14, left: `${pct(current)}%`,
          transform: 'translateX(-50%)',
          display: 'flex', flexDirection: 'column', alignItems: 'center',
        }}>
          <div style={{
            padding: '2px 6px',
            background: 'var(--w-ink)', color: '#FAF8F2',
            borderRadius: 4,
            fontFamily: 'var(--font-mono)', fontSize: 10, fontWeight: 600,
            letterSpacing: '-0.01em',
            whiteSpace: 'nowrap',
          }}>${current}</div>
          <div style={{
            width: 0, height: 0,
            borderLeft: '3px solid transparent', borderRight: '3px solid transparent',
            borderTop: '4px solid var(--w-ink)',
          }} />
          <div style={{ width: 9, height: 9, borderRadius: '50%', background: 'var(--w-ink)', marginTop: -3 }} />
        </div>
        {/* labels */}
        <div style={{
          position: 'absolute', top: 40, left: `${pct(range.low)}%`,
          transform: 'translateX(-50%)',
          fontFamily: 'var(--font-mono)', fontSize: 10, color: 'var(--w-muted)',
        }}>${range.low}</div>
        <div style={{
          position: 'absolute', top: 40, left: `${pct(range.high)}%`,
          transform: 'translateX(-50%)',
          fontFamily: 'var(--font-mono)', fontSize: 10, color: 'var(--w-muted)',
        }}>${range.high}</div>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// Price history sparkline
// ─────────────────────────────────────────────────────────────
function Sparkline({ data, width = 320, height = 80, current }) {
  if (!data || !data.length) return null;
  const vals = data.map(d => d.v);
  const min = Math.min(...vals) - 5;
  const max = Math.max(...vals) + 5;
  const w = width, h = height;
  const px = (i) => (i / (data.length - 1)) * (w - 24) + 12;
  const py = (v) => h - 18 - ((v - min) / (max - min)) * (h - 28);
  const points = data.map((d, i) => `${px(i)},${py(d.v)}`).join(' ');
  const areaPts = `12,${h - 18} ${points} ${w - 12},${h - 18}`;
  const lastIdx = data.length - 1;
  return (
    <svg width={w} height={h} viewBox={`0 0 ${w} ${h}`}>
      <defs>
        <linearGradient id="spark-fill" x1="0" x2="0" y1="0" y2="1">
          <stop offset="0%" stopColor="var(--w-ink)" stopOpacity="0.08" />
          <stop offset="100%" stopColor="var(--w-ink)" stopOpacity="0" />
        </linearGradient>
      </defs>
      <polyline points={areaPts} fill="url(#spark-fill)" stroke="none" />
      <polyline points={points} fill="none" stroke="var(--w-ink)" strokeWidth="1.5" strokeLinejoin="round" strokeLinecap="round" />
      {data.map((d, i) => (
        <circle key={i} cx={px(i)} cy={py(d.v)} r={i === lastIdx ? 3.5 : 1.5}
          fill={i === lastIdx ? 'var(--w-buy)' : 'var(--w-ink)'} />
      ))}
      {data.map((d, i) => (
        <text key={`m${i}`} x={px(i)} y={h - 4} textAnchor="middle"
          fontFamily="var(--font-mono)" fontSize="9" fill="var(--w-muted)">
          {d.m}
        </text>
      ))}
    </svg>
  );
}

Object.assign(window, {
  WorthlyMark, Icon, VerdictPill, Num, SectionLabel, Hairline, ScreenHeader,
  ProductImage, PrimaryButton, Card, PriceBand, Sparkline,
});

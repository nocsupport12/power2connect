<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OIC Health — Co-partner of One Intranet Community</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
:root{
  --b1:#1e3a5f;--b2:#1d5fa6;--b3:#2e7dd1;--b4:#60a5fa;--b5:#dbeafe;--b6:#eff6ff;
  --teal:#0ea5b0;--teal-l:#ccf5f8;
  --ink:#0f172a;--muted:#4b6584;--white:#fff;--surface:#f0f7ff;
}
body{font-family:'DM Sans',sans-serif;background:var(--surface);color:var(--ink);overflow-x:hidden;}

/* NAV */
nav{background:var(--white);border-bottom:1px solid rgba(30,58,95,0.1);padding:0 44px;display:flex;align-items:center;justify-content:space-between;height:66px;position:sticky;top:0;z-index:200;box-shadow:0 1px 12px rgba(30,58,95,0.06);}
.nav-left{display:flex;align-items:center;gap:24px;}
.logo{display:flex;align-items:center;gap:10px;font-weight:700;font-size:15px;color:var(--ink);}
.logo-box{width:36px;height:36px;background:var(--b2);border-radius:10px;display:flex;align-items:center;justify-content:center;}
.logo-box svg{width:20px;height:20px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
.partner-tag{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);background:var(--b6);border:1px solid rgba(30,95,166,0.15);border-radius:20px;padding:4px 12px;}
.partner-dot{width:6px;height:6px;border-radius:50%;background:var(--teal);}
.nav-right{display:flex;align-items:center;gap:10px;}
.nbtn{padding:8px 18px;border-radius:9px;font-size:13px;font-weight:500;font-family:'DM Sans',sans-serif;cursor:pointer;border:none;transition:all .18s;}
.nbtn.ghost{background:transparent;border:1px solid rgba(30,58,95,0.18);color:var(--b2);}
.nbtn.ghost:hover{background:var(--b6);}
.nbtn.fill{background:var(--b2);color:#fff;}
.nbtn.fill:hover{background:var(--b1);}

/* HERO */
.hero-wrap{background:linear-gradient(135deg,var(--b1) 0%,var(--b2) 60%,var(--b3) 100%);}
.hero{padding:72px 44px 0;display:grid;grid-template-columns:1fr 400px;gap:48px;max-width:1140px;margin:0 auto;align-items:flex-end;position:relative;overflow:hidden;}
.hero-bg-circles{position:absolute;inset:0;overflow:hidden;pointer-events:none;}
.hbc{position:absolute;border-radius:50%;border:1px solid rgba(255,255,255,0.06);}
.hbc1{width:400px;height:400px;top:-100px;right:-80px;}
.hbc2{width:260px;height:260px;top:40px;right:80px;}
.hbc3{width:160px;height:160px;bottom:-40px;left:240px;border-color:rgba(255,255,255,0.04);}
.hero-left{position:relative;z-index:2;padding-bottom:64px;}
.hero-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);border-radius:20px;padding:5px 14px;font-size:12px;font-weight:500;color:rgba(255,255,255,0.9);margin-bottom:22px;}
.eyebrow-pulse{width:7px;height:7px;border-radius:50%;background:#7dd3fc;animation:pulse 2s infinite;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.3;}}
h1{font-family:'Playfair Display',serif;font-size:44px;line-height:1.1;color:#fff;margin-bottom:18px;letter-spacing:-.5px;}
h1 span{color:#7dd3fc;}
.hero-p{font-size:14px;color:rgba(255,255,255,0.75);line-height:1.8;max-width:420px;margin-bottom:30px;}
.hero-btns{display:flex;gap:12px;flex-wrap:wrap;}
.hbtn{padding:12px 26px;border-radius:10px;font-size:14px;font-weight:600;font-family:'DM Sans',sans-serif;cursor:pointer;border:none;transition:all .2s;}
.hbtn.wh{background:#fff;color:var(--b2);}
.hbtn.wh:hover{background:var(--b5);transform:translateY(-1px);}
.hbtn.tr{background:rgba(255,255,255,0.12);border:1.5px solid rgba(255,255,255,0.25);color:#fff;}
.hbtn.tr:hover{background:rgba(255,255,255,0.2);}

/* Hero visual panel */
.hero-right{position:relative;z-index:2;align-self:flex-end;}
.health-panel{background:rgba(255,255,255,0.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.18);border-radius:18px 18px 0 0;padding:24px;display:flex;flex-direction:column;gap:14px;}
.hp-title{font-size:12px;font-weight:600;color:rgba(255,255,255,0.6);letter-spacing:1px;text-transform:uppercase;margin-bottom:4px;}
.hp-stat-row{display:flex;gap:10px;}
.hp-stat{flex:1;background:rgba(255,255,255,0.12);border-radius:10px;padding:14px 12px;text-align:center;}
.hp-stat-n{font-size:22px;font-weight:700;color:#fff;}
.hp-stat-l{font-size:11px;color:rgba(255,255,255,0.55);margin-top:2px;}
.hp-bar-row{display:flex;flex-direction:column;gap:8px;}
.hp-bar-item{display:flex;align-items:center;gap:10px;}
.hp-bar-label{font-size:11px;color:rgba(255,255,255,0.6);width:72px;flex-shrink:0;}
.hp-bar-track{flex:1;height:6px;background:rgba(255,255,255,0.1);border-radius:3px;overflow:hidden;}
.hp-bar-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,#7dd3fc,#38bdf8);}
.hp-bar-val{font-size:11px;color:#fff;width:30px;text-align:right;}
.hp-pulse-row{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.08);border-radius:10px;padding:10px 14px;}
.hp-pulse-dot{width:8px;height:8px;border-radius:50%;background:#4ade80;animation:pulse 1.5s infinite;flex-shrink:0;}
.hp-pulse-text{font-size:12px;color:rgba(255,255,255,0.8);}
.hp-pulse-time{margin-left:auto;font-size:11px;color:rgba(255,255,255,0.4);}

/* PARTNER BANNER */
.partner-banner{background:var(--teal-l);border-top:1px solid rgba(14,165,176,0.2);border-bottom:1px solid rgba(14,165,176,0.2);padding:14px 44px;display:flex;align-items:center;gap:16px;}
.pb-icon{width:32px;height:32px;background:var(--teal);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;}
.pb-text{font-size:13px;color:#0c4a4e;line-height:1.5;}
.pb-text strong{font-weight:600;}

/* SERVICES */
.services{max-width:1140px;margin:0 auto;padding:68px 44px;}
.sec-label{font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--b3);margin-bottom:12px;}
.sec-h{font-family:'Playfair Display',serif;font-size:30px;color:var(--ink);margin-bottom:10px;}
.sec-p{font-size:14px;color:var(--muted);line-height:1.7;max-width:520px;margin-bottom:36px;}
.services-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
.svc-card{background:var(--white);border:1px solid rgba(30,58,95,0.1);border-radius:16px;padding:26px;transition:all .2s;position:relative;overflow:hidden;}
.svc-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:3px;background:var(--b3);transform:scaleX(0);transition:transform .3s;transform-origin:left;}
.svc-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(30,58,95,0.1);}
.svc-card:hover::after{transform:scaleX(1);}
.svc-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:16px;}
.ic-blue{background:var(--b6);}
.ic-teal{background:var(--teal-l);}
.ic-soft{background:#fef3c7;}
.svc-card h3{font-size:15px;font-weight:600;color:var(--ink);margin-bottom:8px;}
.svc-card p{font-size:13px;color:var(--muted);line-height:1.7;}

/* FAQ */
.faq-section{background:var(--white);border-top:1px solid rgba(30,58,95,0.08);}
.faq-inner{max-width:1140px;margin:0 auto;padding:68px 44px;display:grid;grid-template-columns:300px 1fr;gap:60px;}
.faq-side-label{font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:var(--b3);margin-bottom:12px;}
.faq-side h2{font-family:'Playfair Display',serif;font-size:26px;line-height:1.3;color:var(--ink);margin-bottom:14px;}
.faq-side p{font-size:13.5px;color:var(--muted);line-height:1.7;}
.faq-list{display:flex;flex-direction:column;gap:10px;}
.faq-item{border:1px solid rgba(30,58,95,0.1);border-radius:12px;overflow:hidden;background:var(--surface);}
.faq-item.open{border-color:var(--b3);background:var(--white);}
.faq-q{width:100%;display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:transparent;border:none;font-family:'DM Sans',sans-serif;font-size:13.5px;font-weight:500;color:var(--ink);cursor:pointer;text-align:left;gap:12px;}
.faq-ico{width:24px;height:24px;border-radius:50%;border:1.5px solid rgba(30,58,95,0.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;color:var(--b2);transition:all .25s;}
.faq-item.open .faq-ico{background:var(--b2);border-color:var(--b2);color:#fff;transform:rotate(45deg);}
.faq-ans{max-height:0;overflow:hidden;transition:max-height .35s ease;}
.faq-ans.open{max-height:200px;}
.faq-ans-in{margin:0 20px;padding:4px 0 18px;border-top:1px solid rgba(30,58,95,0.08);padding-top:14px;font-size:13px;color:var(--muted);line-height:1.75;}

/* MODAL */
.overlay{position:fixed;inset:0;background:rgba(15,23,42,0.55);display:none;align-items:center;justify-content:center;z-index:999;}
.overlay.show{display:flex;}
.modal{background:#fff;border-radius:20px;padding:38px;max-width:500px;width:92%;position:relative;}
.modal-head{display:flex;align-items:center;gap:14px;margin-bottom:18px;}
.modal-ico{width:44px;height:44px;background:var(--b5);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;}
.modal h2{font-family:'Playfair Display',serif;font-size:21px;color:var(--ink);}
.modal p{font-size:13.5px;color:var(--muted);line-height:1.8;margin-bottom:10px;}
.modal-x{position:absolute;top:16px;right:16px;width:30px;height:30px;border-radius:50%;background:var(--b6);border:none;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;color:var(--b2);}
.modal-x:hover{background:var(--b5);}
.modal-foot{margin-top:22px;display:flex;justify-content:flex-end;}

/* FOOTER */
footer{background:var(--b1);padding:28px 44px;display:grid;grid-template-columns:1fr auto;align-items:center;gap:24px;}
.footer-brand{font-weight:700;font-size:15px;color:#fff;margin-bottom:4px;}
.footer-sub{font-size:12px;color:rgba(255,255,255,0.4);}
.footer-partner{font-size:12px;color:rgba(255,255,255,0.5);text-align:right;}
.footer-partner span{color:#7dd3fc;}

/* Rise animation */
.rise{opacity:0;transform:translateY(16px);transition:opacity .5s ease,transform .5s ease;}
.rise.in{opacity:1;transform:none;}

/* Responsive */
@media(max-width:900px){
  nav{padding:0 20px;}
  .partner-tag{display:none;}
  .hero{grid-template-columns:1fr;padding:48px 20px 0;}
  .hero-right{display:none;}
  h1{font-size:32px;}
  .services-grid{grid-template-columns:1fr;}
  .faq-inner{grid-template-columns:1fr;gap:32px;padding:48px 20px;}
  .services{padding:48px 20px;}
  footer{grid-template-columns:1fr;text-align:center;padding:24px 20px;}
  .footer-partner{text-align:center;}
  .partner-banner{padding:14px 20px;}
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-left">
    <div class="logo">
      <div class="logo-box">
        <svg viewBox="0 0 20 20"><path d="M10 3v14M3 10h14"/><circle cx="10" cy="10" r="3"/></svg>
      </div>
      OIC Health
    </div>
    <div class="partner-tag"><span class="partner-dot"></span>Co-partner of One Intranet Community</div>
  </div>
  <div class="nav-right">
    <button class="nbtn ghost" onclick="document.getElementById('services').scrollIntoView({behavior:'smooth'})">Services</button>
    <button class="nbtn ghost" onclick="document.getElementById('faq').scrollIntoView({behavior:'smooth'})">About</button>
    <button class="nbtn fill" onclick="window.location='chatbot.php'">← Back to AI Chatbot</button>
  </div>
</nav>

<!-- HERO -->
<div class="hero-wrap">
  <div class="hero rise" id="r0">
    <div class="hero-bg-circles">
      <div class="hbc hbc1"></div>
      <div class="hbc hbc2"></div>
      <div class="hbc hbc3"></div>
    </div>
    <div class="hero-left">
      <div class="hero-eyebrow"><span class="eyebrow-pulse"></span>Healthcare · Powered by Connectivity</div>
      <h1>Your Health,<br><span>Connected</span><br>&amp; Cared For</h1>
      <p class="hero-p">OIC Health is the healthcare arm of One Intranet Community — bringing accessible medical support, telehealth services, and community wellness programs to every home we connect.</p>
      <div class="hero-btns">
        <button class="hbtn wh" onclick="document.getElementById('modal').classList.add('show')">Our Mission</button>
        <button class="hbtn tr" onclick="document.getElementById('services').scrollIntoView({behavior:'smooth'})">Explore Services ↓</button>
      </div>
    </div>
    <div class="hero-right">
      <div class="health-panel">
        <div class="hp-title">Community Health Dashboard</div>
        <div class="hp-stat-row">
          <div class="hp-stat"><div class="hp-stat-n">4.8k</div><div class="hp-stat-l">Patients served</div></div>
          <div class="hp-stat"><div class="hp-stat-n">98%</div><div class="hp-stat-l">Satisfaction</div></div>
          <div class="hp-stat"><div class="hp-stat-n">24/7</div><div class="hp-stat-l">Telehealth</div></div>
        </div>
        <div class="hp-bar-row">
          <div class="hp-bar-item">
            <div class="hp-bar-label">Consultations</div>
            <div class="hp-bar-track"><div class="hp-bar-fill" style="width:82%"></div></div>
            <div class="hp-bar-val">82%</div>
          </div>
          <div class="hp-bar-item">
            <div class="hp-bar-label">Wellness</div>
            <div class="hp-bar-track"><div class="hp-bar-fill" style="width:65%"></div></div>
            <div class="hp-bar-val">65%</div>
          </div>
          <div class="hp-bar-item">
            <div class="hp-bar-label">Rural reach</div>
            <div class="hp-bar-track"><div class="hp-bar-fill" style="width:74%"></div></div>
            <div class="hp-bar-val">74%</div>
          </div>
        </div>
        <div class="hp-pulse-row">
          <div class="hp-pulse-dot"></div>
          <div class="hp-pulse-text">AI health assistant is online</div>
          <div class="hp-pulse-time">Live</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- PARTNER BANNER -->
<div class="partner-banner rise" id="r1">
  <div class="pb-icon"></div>
  <div class="pb-text"><strong>Official co-partner of One Intranet Community</strong> — We leverage OIC's internet infrastructure to deliver telehealth services directly to underserved and rural communities across the region.</div>
</div>

<!-- SERVICES -->
<section id="services">
  <div class="services">
    <div class="sec-label rise" id="r2">What we offer</div>
    <h2 class="sec-h rise" id="r3">Healthcare built for every community</h2>
    <p class="sec-p rise" id="r4">From telehealth consultations to community wellness programs — OIC Health uses One Intranet Community's connectivity to bring quality healthcare where it's needed most.</p>
    <div class="services-grid">
      <div class="svc-card rise" id="r5">
        <div class="svc-icon ic-blue">🩺</div>
        <h3>Telehealth Consultations</h3>
        <p>Connect with licensed doctors and specialists from your home — no travel needed. Powered by OIC's high-speed internet infrastructure.</p>
      </div>
      <div class="svc-card rise" id="r6">
        <div class="svc-icon ic-teal"></div>
        <h3>Medication Guidance</h3>
        <p>AI-assisted medication reminders, dosage information, and prescription support — available 24/7 through our health chatbot.</p>
      </div>
      <div class="svc-card rise" id="r7">
        <div class="svc-icon ic-blue"></div>
        <h3>Community Clinics</h3>
        <p>On-the-ground health services in rural areas, working hand-in-hand with local health centers and barangay officials.</p>
      </div>
      <div class="svc-card rise" id="r8">
        <div class="svc-icon ic-soft"></div>
        <h3>Wellness Programs</h3>
        <p>Mental health support, nutrition counseling, and preventive care programs tailored to underserved communities.</p>
      </div>
      <div class="svc-card rise" id="r9">
        <div class="svc-icon ic-teal"></div>
        <h3>Health Records</h3>
        <p>Secure digital health records accessible anywhere — made possible through OIC's reliable internet connectivity.</p>
      </div>
      <div class="svc-card rise" id="r10">
        <div class="svc-icon ic-blue"></div>
        <h3>AI Health Chatbot</h3>
        <p>Real-time answers to health queries. When the AI can't help, a care coordinator follows up directly through a call.</p>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="faq-section" id="faq">
  <div class="faq-inner">
    <div class="rise" id="r11">
      <div class="faq-side-label">About OIC Health</div>
      <h2 class="faq-side">Frequently asked questions</h2>
      <p class="faq-side" style="margin-top:12px;">Everything you need to know about our healthcare services and our partnership with One Intranet Community.</p>
    </div>
    <div class="faq-list rise" id="r12">
      <div class="faq-item open">
        <button class="faq-q" onclick="toggleFaq(this)">What is OIC Health?<div class="faq-ico">+</div></button>
        <div class="faq-ans open"><div class="faq-ans-in">OIC Health is the official healthcare co-partner of One Intranet Community. We use OIC's internet infrastructure to deliver telehealth, wellness, and AI-assisted health services to residential, commercial, and underserved communities.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">How does the AI health chatbot work?<div class="faq-ico">+</div></button>
        <div class="faq-ans"><div class="faq-ans-in">Our AI chatbot provides immediate and accurate responses to health-related queries. If it fails to satisfy a concern, the conversation is recorded and a health coordinator follows up directly through a call or further assistance.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">Is my health data secure?<div class="faq-ico">+</div></button>
        <div class="faq-ans"><div class="faq-ans-in">Yes. All health information you provide is securely stored in our encrypted database and is only used to provide you with better care and follow-up services.</div></div>
      </div>
      <div class="faq-item">
        <button class="faq-q" onclick="toggleFaq(this)">Who can access OIC Health services?<div class="faq-ico">+</div></button>
        <div class="faq-ans"><div class="faq-ans-in">Our services are available to all communities — with a special focus on rural and underserved areas that have historically lacked access to quality healthcare. Regardless of your status in life, you have the right to health.</div></div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div>
    <div class="footer-brand">OIC Health</div>
    <div class="footer-sub">© 2025 OIC Health · All rights reserved</div>
  </div>
  <div class="footer-partner">Official co-partner of<br><span>One Intranet Community</span></div>
</footer>

<!-- MISSION MODAL -->
<div class="overlay" id="modal" onclick="if(event.target===this)this.classList.remove('show')">
  <div class="modal">
    <button class="modal-x" onclick="document.getElementById('modal').classList.remove('show')">✕</button>
    <div class="modal-head">
      <div class="modal-ico"></div>
      <h2>Our Mission</h2>
    </div>
    <p>OIC Health exists to ensure that every community connected by One Intranet Community also has access to quality healthcare. We believe that connectivity and health go hand in hand — a connected community is a healthier community.</p>
    <p>Through telehealth, AI-assisted care, and on-the-ground wellness programs, we bridge the gap between patients and providers — especially in rural and underserved areas where healthcare has historically been out of reach.</p>
    <p>"Regardless of your status in life, you have the power to connect — and the right to be healthy."</p>
    <div class="modal-foot">
      <button class="hbtn wh" style="background:var(--b2);color:#fff;" onclick="document.getElementById('modal').classList.remove('show')">Close</button>
    </div>
  </div>
</div>

<script>
function toggleFaq(btn){
  const item=btn.closest('.faq-item');
  const ans=item.querySelector('.faq-ans');
  const isOpen=item.classList.contains('open');
  document.querySelectorAll('.faq-item').forEach(i=>{
    i.classList.remove('open');
    i.querySelector('.faq-ans').classList.remove('open');
  });
  if(!isOpen){item.classList.add('open');ans.classList.add('open');}
}

const obs=new IntersectionObserver(entries=>{
  entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in');obs.unobserve(e.target);}});
},{threshold:0.08});
document.querySelectorAll('.rise').forEach(el=>obs.observe(el));

document.addEventListener("dragstart",e=>e.preventDefault());
document.addEventListener("contextmenu",e=>e.preventDefault());
document.addEventListener("keydown",e=>{
  if(e.key==="F12"||(e.ctrlKey&&e.shiftKey&&["I","J","C"].includes(e.key))||(e.ctrlKey&&e.key==="u"))e.preventDefault();
});
</script>
</body>
</html>
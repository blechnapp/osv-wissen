# Zeigt lokale Seiten im echten Rahmen von Set 78 (Kopf, Menü, Footer): rendert vorschau/*.html in die FAQ-Vorschauseite,
# Bilder des Plugins als data-URI, aktuelles CSS aus Partials/Stil.twig. Ergebnis: belege/shopvorschau/index.html
import pathlib, base64, sys, datetime
from playwright.sync_api import sync_playwright
P = pathlib.Path(__file__).resolve().parent.parent
U = "https://www.original-seiffener-volkskunst.de/faq/?pluginSetPreview=JDJ5JDEwJGFSVi9MSUxVeVlmVFliMWgxYnN5OE82eFRabFVpZFlOQ0RlMnJza3pzbFEvYTBpemhSTEky&contentLinkIds[]=10372&contentLinkIds[]=10630&contentLinkIds[]=10418"
seiten = sys.argv[1:] or ["videos", "ratgeber", "ratgeber-raeuchermaennchen-anzuenden"]
D = P / "belege/shopvorschau"; D.mkdir(parents=True, exist_ok=True)
css = (P / "resources/views/Partials/Stil.twig").read_text().replace("<style>", "").replace("</style>", "")
def inhalt(name):
    h = (P / "vorschau" / (name + ".html")).read_text()
    a = h.index("<div class=inhalt>") + len("<div class=inhalt>"); e = h.index("</div><style>", a)
    x = h[a:e]
    for f in (P / "resources/images/video").glob("*.webp"):
        x = x.replace("../resources/images/video/" + f.name, "data:image/webp;base64," + base64.b64encode(f.read_bytes()).decode())
    return x
stand = datetime.datetime.now().strftime("%d.%m.%Y %H:%M")
with sync_playwright() as p:
    b = p.chromium.launch()
    for name, vp, mob in [("rechner", {"width": 1440, "height": 900}, False), ("handy", {"width": 390, "height": 844}, True)]:
        c = b.new_context(viewport=vp, is_mobile=mob, device_scale_factor=2 if mob else 1); pg = c.new_page()
        pg.goto(U, wait_until="domcontentloaded", timeout=120000); pg.wait_for_timeout(6000)
        try: pg.click("button:has-text('Alle ablehnen')", timeout=2000)
        except Exception: pass
        pg.evaluate("css=>{const s=document.createElement('style');s.textContent=css;document.head.appendChild(s);[...document.querySelectorAll('body *')].filter(d=>/^plentyShop LTS-Vorschau/.test(d.textContent.trim())&&d.getBoundingClientRect().width<400).forEach(d=>d.style.display='none')}", css)
        for s in seiten:
            pg.evaluate("h=>{const w=document.querySelector('.osvw-seite');w.outerHTML=h;window.scrollTo(0,0)}", inhalt(s))
            pg.wait_for_timeout(900)
            pg.screenshot(path=str(D / f"{s}_{name}.png"), full_page=True)
        c.close()
    b.close()
v = int(datetime.datetime.now().timestamp())
(D / "index.html").write_text("<!doctype html><html lang=de><meta charset=utf-8><title>Shopvorschau OSVWissen</title><style>body{margin:0;background:#e9e6e1;font-family:-apple-system,Helvetica,Arial,sans-serif}h1{font-size:16px;padding:12px 20px;margin:0;background:#0c2a5e;color:#fff}h2{margin:24px 20px 0;font-size:18px}.reihe{display:flex;gap:30px;align-items:flex-start;padding:12px 20px}.reihe div{background:#fff;box-shadow:0 4px 20px rgba(0,0,0,.15)}.reihe p{margin:0;padding:8px 12px;font-weight:600}.r img{width:1000px;display:block}.h img{width:390px;display:block}nav{padding:10px 20px}nav a{margin-right:16px}</style>"
    + f"<h1>OSVWissen im Rahmen von Set 78 (lokaler Stand {stand}, noch nicht im Shop)</h1><nav>" + "".join(f'<a href="#{s}">{s}</a>' for s in seiten) + "</nav>"
    + "".join(f'<h2 id="{s}">{s}</h2><div class=reihe><div class=r><p>Rechner</p><img src="{s}_rechner.png?v={v}"></div><div class=h><p>Handy</p><img src="{s}_handy.png?v={v}"></div></div>' for s in seiten))
print(D / "index.html")

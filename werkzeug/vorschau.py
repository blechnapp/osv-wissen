# Lokale Vorschau: rendert die echten Twig-Vorlagen (Inhalt/, so wie osvw_faq() und osvw_kurzhilfe() im Shop) mit Jinja2 und denselben Regeln wie WissenService.
# Ergebnis: vorschau/faq.html und vorschau/kontakt.html (nicht im Git).
import json, pathlib, re, datetime, jinja2
P = pathlib.Path(__file__).resolve().parent.parent
d = json.loads((P / "daten/wissen.json").read_text())
heute = datetime.date.today().isoformat()
def aufbereiten(b):
    bl = []
    for a in b["absaetze"]:
        a = a.strip()
        if not a: continue
        if a.startswith("- "):
            if bl and bl[-1]["typ"] == "ul": bl[-1]["punkte"].append(a[2:])
            else: bl.append({"typ": "ul", "punkte": [a[2:]]})
        elif re.fullmatch(r"https?://\S+", a):
            bl.append({"typ": "link", "url": a, "text": re.sub(r"^https?://(www\.)?", "", a.rstrip("/"))})
        else: bl.append({"typ": "p", "text": a})
    return dict(b, bloecke=bl, klartext=" ".join(x.lstrip("- ") for x in b["absaetze"]))
def ort(o): return [aufbereiten(b) for b in d["bausteine"] if o in b["orte"] and (not b.get("bis") or heute <= b["bis"])]
faq = ort("faq")
themen = [dict(t, fragen=[b for b in faq if b["thema"] == t["id"]]) for t in d["themen"]]
themen = [t for t in themen if t["fragen"]]
schema = json.dumps({"@context": "https://schema.org", "@type": "FAQPage", "mainEntity": [{"@type": "Question", "name": b["frage"], "acceptedAnswer": {"@type": "Answer", "text": b["klartext"]}} for t in themen for b in t["fragen"]]}, ensure_ascii=False)
class Lader(jinja2.BaseLoader):
    def get_source(self, env, name):
        rel = name.replace("OSVWissen::", "").replace(".", "/") + ".twig"
        s = (P / "resources/views" / rel).read_text()
        s = s.replace("{% elseif", "{% elif")
        s = re.sub(r"\{\{ ([^{}?]+?) \? '([^']*)' : '([^']*)' \}\}", r"{{ '\2' if \1 else '\3' }}", s)
        s = s.replace("| replace({' ': '', '-': ''})", "| ohneleer")
        return s, None, lambda: True
env = jinja2.Environment(loader=Lader(), autoescape=True)
env.filters["raw"] = lambda x: jinja2.Markup(x) if hasattr(jinja2, "Markup") else __import__("markupsafe").Markup(x)
env.globals["plugin_path"] = lambda n: "../resources"
env.filters["ohneleer"] = lambda x: x.replace(" ", "").replace("-", "")
widget = {"settings": {"customClass": {"mobile": ""}}}
beilage = env.get_template('OSVWissen::Partials.Stil').render() + env.get_template('OSVWissen::Containers.WissenSkript').render()
rahmen = '<!doctype html><html lang=de><meta charset=utf-8><meta name=viewport content="width=device-width,initial-scale=1"><title>{t}</title><style>body{{margin:0;font-family:-apple-system,Helvetica,Arial,sans-serif;background:#fff}}.kopf{{background:#0c2a5e;color:#fff;padding:14px 16px;font-weight:700}}.inhalt{{max-width:1200px;margin:0 auto;padding:24px 16px}}</style><body><div class=kopf>Vorschau OSVWissen · {t} (lokal, nicht der Shop)</div><div class=inhalt>{x}</div>{b}</body></html>'
(P / "vorschau").mkdir(exist_ok=True)
(P / "vorschau/faq.html").write_text(rahmen.format(t="Häufige Fragen", b=beilage, x=env.get_template("OSVWissen::Inhalt.Faq").render(themen=themen, kontaktUrl="/kontakt/", telefon="+49 37362 774-0", email="info@original-seiffener-volkskunst.de", zeiten="Mo–Fr 10–16 Uhr", videoKanal="https://www.youtube.com/channel/UC55qqOuVezJhwKjXpJV8irg")))
(P / "vorschau/kontakt.html").write_text(rahmen.format(t="Kurzhilfe Kontaktseite", b=beilage, x=env.get_template("OSVWissen::Inhalt.Kurzhilfe").render(fragen=ort("kontakt"), faqUrl="faq.html")))
def woerter(s): return len(s.split()) if s.strip() else 0
bs = {b["id"]: b for b in d["bausteine"]}
def ratgeber_voll(r):
    r = dict(r); n = woerter(r.get("einleitung", "")); ab = []
    for a in r["abschnitte"]:
        abs_ = bs[a["baustein"]]["absaetze"] if "baustein" in a else a.get("absaetze", [])
        if a.get("liste"): abs_ = [x if x.startswith("- ") or re.match(r"https?://", x) else "- " + x for x in abs_]
        f = aufbereiten({"absaetze": abs_}); n += woerter(f["klartext"]); ab.append({"titel": a["titel"], "bloecke": f["bloecke"]})
    r["abschnitte"] = ab; r["lesezeit"] = max(1, -(-n // 180))
    r["videoDaten"] = next((v for v in d.get("videos", []) if v["youtube"] == r.get("video")), None)
    return r
liste = [dict(id=r["id"], titel=r["titel"], kurz=r["kurz"], marke=r["marke"], bild=r["bild"], lesezeit=ratgeber_voll(r)["lesezeit"]) for r in d.get("ratgeber", [])]
rg = dict(basis="ratgeber-", liste=liste, zeiten="Mo–Fr 10–16 Uhr", telefon="+49 37362 774-0", faqUrl="faq.html", videosUrl="videos.html")
(P / "vorschau/ratgeber.html").write_text(rahmen.format(t="Gut zu wissen", b=beilage, x=env.get_template("OSVWissen::Inhalt.RatgeberUebersicht").render(**rg)))
for r in d.get("ratgeber", []):
    (P / ("vorschau/ratgeber-" + r["id"] + ".html")).write_text(rahmen.format(t=r["titel"], b=beilage, x=env.get_template("OSVWissen::Inhalt.Ratgeber").render(r=ratgeber_voll(r), **rg)))
(P / "vorschau/videos.html").write_text(rahmen.format(t="Videos", b=beilage, x=env.get_template("OSVWissen::Inhalt.Videos").render(videos=d.get("videos", []), kanal="https://www.youtube.com/channel/UC55qqOuVezJhwKjXpJV8irg", zeiten="Mo–Fr 10–16 Uhr", telefon="+49 37362 774-0", faqUrl="faq.html", ratgeberUrl="/ratgeber/")))
print("Vorschau:", P / "vorschau/faq.html")

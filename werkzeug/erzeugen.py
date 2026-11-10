# Erzeugt src/Daten/Bausteine.php aus daten/wissen.json. Nach jeder Textänderung ausführen, dann Version hochzählen.
# Prüft dabei: eindeutige IDs, bekanntes Thema, bekannte Orte, Datum bis im Format JJJJ-MM-TT.
import json, pathlib, re, sys
P = pathlib.Path(__file__).resolve().parent.parent
d = json.loads((P / "daten/wissen.json").read_text())
themen = {t["id"] for t in d["themen"]}
fehler = []
gesehen = set()
for b in d["bausteine"]:
    if b["id"] in gesehen: fehler.append("doppelte ID " + b["id"])
    gesehen.add(b["id"])
    if b["thema"] not in themen: fehler.append(b["id"] + ": unbekanntes Thema " + b["thema"])
    for o in b["orte"]:
        if o not in ("faq", "kontakt", "artikel"): fehler.append(b["id"] + ": unbekannter Ort " + o)
    if b.get("bis") and not re.fullmatch(r"\d{4}-\d{2}-\d{2}", b["bis"]): fehler.append(b["id"] + ": bis falsch")
    if "artikel" in b["orte"] and not b.get("kategorien"): fehler.append(b["id"] + ": Ort artikel ohne Kategorien")
ytids = set()
for v in d.get("videos", []):
    if not re.fullmatch(r"[\w-]{11}", v["youtube"]): fehler.append("Video-ID falsch: " + v["youtube"])
    if v["youtube"] in ytids: fehler.append("Video doppelt: " + v["youtube"])
    ytids.add(v["youtube"])
    if not (P / "resources/images/video" / (v["youtube"] + ".webp")).exists(): fehler.append("Vorschaubild fehlt: resources/images/video/" + v["youtube"] + ".webp")
    for x in v.get("dazu", []):
        if not x["url"].startswith("/"): fehler.append(v["youtube"] + ": dazu-Adresse muss mit / beginnen")
rids = set()
for r in d.get("ratgeber", []):
    if not re.fullmatch(r"[a-z0-9-]+", r["id"]): fehler.append("Ratgeber-ID nur a-z, 0-9, -: " + r["id"])
    if r["id"] in rids or r["id"] == "uebersicht": fehler.append("Ratgeber-ID doppelt/reserviert: " + r["id"])
    rids.add(r["id"])
    for a in r["abschnitte"]:
        if "baustein" in a and a["baustein"] not in gesehen: fehler.append(r["id"] + ": unbekannter Baustein " + a["baustein"])
    if r.get("video") and r["video"] not in ytids: fehler.append(r["id"] + ": Video nicht in videos: " + r["video"])
if fehler:
    print("\n".join(fehler)); sys.exit(1)
def php(v):
    if isinstance(v, dict): return "[" + ", ".join(php(k) + " => " + php(x) for k, x in v.items()) + "]"
    if isinstance(v, list): return "[" + ", ".join(php(x) for x in v) + "]"
    if isinstance(v, bool): return "true" if v else "false"
    if isinstance(v, int): return str(v)
    return "'" + str(v).replace("\\", "\\\\").replace("'", "\\'") + "'"
daten = {"stand": d["stand"], "themen": d["themen"],
         "bausteine": [{k: v for k, v in b.items() if k != "pruefen"} for b in d["bausteine"]],
         "videos": [{k: x for k, x in v.items() if k != "pruefen"} for v in d.get("videos", [])],
         "ratgeber": [{k: x for k, x in r.items() if k != "pruefen"} for r in d.get("ratgeber", [])]}
(P / "src/Daten/Bausteine.php").write_text(
    "<?php\n\nnamespace OSVWissen\\Daten;\n\n/**\n * ERZEUGT aus daten/wissen.json mit werkzeug/erzeugen.py – nicht von Hand ändern.\n */\nclass Bausteine\n{\n    public static function alle(): array\n    {\n        return " + php(daten) + ";\n    }\n}\n")
print(len(d["bausteine"]), "Bausteine,", len(d["themen"]), "Themen,", len(d.get("videos", [])), "Videos,", len(d.get("ratgeber", [])), "Ratgeber")

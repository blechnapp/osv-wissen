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
if fehler:
    print("\n".join(fehler)); sys.exit(1)
def php(v):
    if isinstance(v, dict): return "[" + ", ".join(php(k) + " => " + php(x) for k, x in v.items()) + "]"
    if isinstance(v, list): return "[" + ", ".join(php(x) for x in v) + "]"
    if isinstance(v, bool): return "true" if v else "false"
    if isinstance(v, int): return str(v)
    return "'" + str(v).replace("\\", "\\\\").replace("'", "\\'") + "'"
daten = {"stand": d["stand"], "themen": d["themen"],
         "bausteine": [{k: v for k, v in b.items() if k != "pruefen"} for b in d["bausteine"]]}
(P / "src/Daten/Bausteine.php").write_text(
    "<?php\n\nnamespace OSVWissen\\Daten;\n\n/**\n * ERZEUGT aus daten/wissen.json mit werkzeug/erzeugen.py – nicht von Hand ändern.\n */\nclass Bausteine\n{\n    public static function alle(): array\n    {\n        return " + php(daten) + ";\n    }\n}\n")
print(len(d["bausteine"]), "Bausteine,", len(d["themen"]), "Themen")

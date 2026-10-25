# Verifica aziende per Verel (CDMO cosmetico italiano) — Cosmoprof Asia

Contesto: Verel è un terzista (CDMO) italiano di skincare/haircare/oral care. Cerchiamo brand owner B2C
con fatturato €5–100M, posizionamento medio-alto, che producono tramite terzisti (non in-house).

Per ciascuna azienda assegnata raccogli SOLO dati con fonte verificabile (URL). Se non trovi, scrivi null.
Non stimare, non inferire. Una fonte ufficiale batte una terza parte.

Gerarchia fonti (usa le migliori disponibili):
- Bilanci ufficiali / registri: Corea DART (dart.fss.or.kr), Giappone EDINET o IR aziendale / kabutan,
  Germania Bundesanzeiger / northdata.de, UK Companies House, Spagna BORME / einforma / infocif / empresia,
  Francia Pappers / societe.com, Australia ASIC, HK/Singapore registri, Brasile, Thailandia DBD.
- Dati di bilancio riportati da portali: Corea Saramin (saramin.co.kr, sezione 기업정보 매출액), JobKorea,
  Catch (catch.co.kr), NICE BizInfo, 잡플래닛; Giappone 帝国データバンク/東京商工リサーチ snippet.
- Stampa economica / comunicati con cifre.
- Dichiarazioni dell'azienda (sito, IR, deck) — marcale come "dichiarato".

Chi produce:
- Corea: le etichette riportano per legge il produttore (제조업자 / 제조원) e il titolare (책임판매업자).
  Cercalo nelle schede prodotto su Olive Young, Naver Smartstore, Coupang, sito ufficiale (sezione 상품정보제공고시).
  Nomi tipici di terzisti: Cosmax, Kolmar Korea, Cosmecca, Kolmar BNH, Hankook Cosmetics Manufacturing, ecc.
  Se 제조업자 = l'azienda stessa → produzione propria.
- Giappone: 製造販売元 vs 製造元 in etichetta.
- EU: "made by / fabriqué par / fabricado por" in etichetta, stabilimento nei bilanci, CPNP.
Prezzo: prezzo al pubblico e formato del prodotto di punta (per stimare posizionamento).

Budget: circa 4–6 ricerche per azienda. Se dopo il budget non trovi nulla, passa oltre.

Output: scrivi un file JSON (array) al percorso indicato, un oggetto per azienda con ESATTAMENTE queste chiavi:
{
 "azienda": "",               // nome come fornito
 "sito_verificato": "",       // dominio ufficiale corretto o null
 "ragione_sociale": "",       // nome legale (anche in lingua locale)
 "fatturato": null,           // numero nella valuta originale (es. 12300000000)
 "valuta": "",                // KRW, JPY, EUR, ...
 "fatturato_eur_m": null,     // conversione approssimata in milioni di euro (KRW/1500, JPY/160, USD/1.08, GBP*1.17 ecc.)
 "anno": null,
 "fatturato_tipo": "",        // "bilancio ufficiale" | "portale da bilancio" | "stampa" | "dichiarato" | null
 "fatturato_fonte": "",       // URL
 "dipendenti": null,
 "dipendenti_fonte": "",
 "produzione": "",            // "terzista: <nome>" | "in-house" | "misto" | "ignoto"
 "produzione_fonte": "",
 "proprieta": "",             // indipendente / gruppo <nome> / VC <nome> / quotata
 "prezzo_punta": "",          // es. "Serum 30ml ₩38.000"
 "note": "",                  // max 200 caratteri, fatti rilevanti per Verel (export UE, canale, lanci)
 "confidenza": ""             // "alta" (bilancio ufficiale), "media" (portale/stampa), "bassa" (solo dichiarato o nulla)
}
Alla fine rispondi con un riepilogo di max 10 righe: per ogni azienda fatturato €M, dipendenti, produzione, confidenza.

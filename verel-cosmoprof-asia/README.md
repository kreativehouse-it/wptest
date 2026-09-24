# Verel – selezione espositori Cosmoprof Asia

Stato al 24/09/2026.

- `verel_cosmoprof_asia_v2.csv`: **file principale**. 100 aziende A/B con nuovo tier (Tier 1 / Tier 2 / Non verificato / Escluso),
  fatturato, dipendenti, produzione, fonti e confidenza.
- `step1_apollo.csv`: arricchimento Apollo (35 aziende trovate su 83).
- `shortlist_verel_cosmoprof_asia.csv`: prima shortlist, fatta solo sul file di input (superata dalla v2).
- `dati_grezzi/`: input originale, istruzioni di ricerca e risultati JSON per lotto.

## Limiti della v2
Ricerca fatta solo sui riassunti dei risultati di ricerca: la rete bloccava l'apertura delle fonti e le 200 ricerche della sessione sono finite.
1 dato da bilancio ufficiale, 15 a confidenza media, 84 bassa.

## Prompt per completare (nuova sessione con rete sbloccata)
Leggi verel-cosmoprof-asia/README.md e verel_cosmoprof_asia_v2.csv, poi:
1. Tier 1 e 2: conferma fatturato e dipendenti da bilancio/registro (DART, Saramin/Catch, Bundesanzeiger/northdata,
   Companies House, registro imprese IT, EDINET/kabutan) e chi produce (per i coreani 제조업자 su Olive Young / Naver / Coupang).
2. Righe "Non verificato", partendo dalle priorità originali A: Deardot, FICC, FIORESE, JK Cos, SOONNOC, The Skin's, TMC Korea,
   Moim/Bettura; poi Dr.SANTE, Wakan, Lindsay & Cos, Celebon, Corelix, Repit.
3. Per ogni dato: valore, anno, URL, confidenza (alta = bilancio, media = portale/stampa, bassa = dichiarato). Niente stime.
4. Salva verel_cosmoprof_asia_v3.csv con una sintesi delle variazioni. Lavora a lotti di 10 e controlla il consumo delle ricerche.

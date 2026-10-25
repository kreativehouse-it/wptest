# Il lavoro nel Drive sostituisce la lista v2 come base dei contatti

Stato al 25/10/2026. Cartella analizzata: **Drive › COSMOPACK-Hong Kong** (Kreativehouse).
Legenda: **[Inferenza]**, **[Speculazione]**, **[Non verificato]**.

## 1. Cosa si adotta e cosa si scarta

| File nel Drive | Data | Decisione | Perché |
|---|---|---|---|
| `05 Verel_CosmoprofAsia2026 – contatti aziende A` | 08/10 | **Adottato**: lista contatti per Diego | 21 aziende A con email da usare, fonte (Apollo, Clay, sito) e note di controllo. La verifica dei nomi del 26/10 è già fatta per queste. |
| `03 Verel_CosmoprofAsia2026_prospect (formato come file Eugenio)` | 28/09-08/10 | **Adottato**: base dei segmenti | S1: 4 A / 20 B / 168 C · S2: 6 A / 23 B / 38 C · S3: 17 A / 60 B / 110 C. Foglio "Decision maker": 446 aziende, 219 contatti consigliati sbloccati su Apollo. Foglio "Percorso HKCEC 13-11" (giro stand). |
| `Verel_APAC_Retailer_Prospects R2` | 22/09 | **Adottato** per il segmento 2, con le correzioni della sezione 3 | 25 retailer sopra i 500 punti vendita con fonti. |
| `Verel_Cosmoprof_Asia_stakeholder_scan` (DE/CH) | 21/09 | **Adottato in parte** | Epigeneva ed EVENSWISS sono confermati come espositori 2026 nel [SWISS Pavilion](https://www.s-ge.com/export/en/cosmoprof-asia-2026-swiss-pavilion-exhibitor-directory). I tedeschi sono confermati solo per il 2025. |
| `verel_cosmoprof_asia_v2.csv` (questo repo) | 24/09 | **Superato** dal file 03 | Più vecchio. In un caso è in contraddizione: **AXXZIA** qui è Tier 2, nel Drive è esclusa (fabbrica propria Yuit Laboratories). Vale il Drive. |
| 00, 01, 02, 04 e ricerche R1-R3 | 21-25/09 | Non usati | Versioni precedenti, superate dal file 03. Non le ho aperte. |

**Limite.** Dei fogli del file 03 ho letto la struttura e le prime righe, non tutte le righe. Il file 05, il documento retailer e lo scan DE/CH li ho letti completi.

## 2. Sei punti dei materiali Drive violano i vincoli di Eugenio

| # | Dove | Problema | Correzione |
|---|---|---|---|
| 1 | File 03, foglio "Percorso HKCEC 13-11", tappa 1 (Banobagi) | Il gancio dice "Verel offre solari con test…" | Riscrivere il gancio: attivi botanici proprietari per la linea derma; nessun riferimento ai solari. |
| 2 | Documento retailer R2 | Proposte con "sun care Made in Italy" per quasi tutti i retailer | Togliere sun care da tutte le proposte finché Verel non produce solari. |
| 3 | Scan DE/CH | Proposte SPF a Schloßwald e MALU WILZ | Come sopra. |
| 4 | Scan DE/CH, "Prossime mosse" | "Verificare in HubSpot" | Eugenio ha escluso HubSpot: la deduplicazione si fa in Apollo e MailUp. |
| 5 | Scan DE/CH e documento retailer | Ruoli procurement e operations tra quelli da contattare | Esclusi dai criteri: solo founder, R&D, marketing prodotto (per i retailer: own brand / product development, vedi domanda R2 in `04`). |
| 6 | Documento retailer R2 | Avverte che il time to market di 6-8 settimane non va usato commercialmente senza conferma interna | La variante B dei template (`03`) resta sospesa finché Verel non la conferma (domanda C8 in `04`). |

## 3. La lista A del file 05 ha quattro casi da rivedere prima dell'invio

| Azienda | Segm. | Problema | Proposta |
|---|---|---|---|
| Allies of Skin (SG) | S3 | Il founder si è dimesso a settembre 2026: non contattarlo | Contattare il COO indicato nel file 05; tenere la priorità A solo se il brand resta indipendente. |
| Dr. Ceuracle (KR) | S3 | Dominio .com in vendita, .co.kr parcheggiato | Non inviare finché il sito globale non è verificato a mano. |
| Ultra Violette e Naked Sundays (AU) | S3 | Brand centrati sui solari, o con forte peso dei solari **[Inferenza: da conoscenza generale, non dalla cartella]** | Scendere a C finché Verel non produce solari, oppure proporre solo skincare non SPF. Decide Eugenio. |
| MALU WILZ Beauté (DE) | S1 | Fa parte del gruppo ARTDECO (circa €107M di ricavi nel 2021, dallo scan DE/CH) | Verificare contro la regola "grandi gruppi esclusi". Decide Eugenio. |

Note utili già nel file 05: Go-To Skincare (le email trovate da Clay sono errate: usare LinkedIn), Ultra Violette (il dominio giusto è .com.au), KSECRET (ksecret.com è un rivenditore, non il brand), Mixsoon (il founder è su Apollo ma senza email).

## 4. Lista di lavoro per il segmento 1 e il segmento 3 (livello azienda)

Le email e i nomi restano nel file 05 del Drive e in Apollo: in questo repo e nell'app non vanno dati personali.

| Segm. | Priorità | Azienda | Paese | Canale disponibile (file 05) |
|---|---|---|---|---|
| S1 | A | SOONNOC | KR | Ufficio export (CEO da confermare) |
| S1 | A | Banobagi | KR | Ufficio vendite estero; CEO confermato |
| S1 | A | Dr. med. Christine Schrammek Kosmetik | DE | Head of Cosmetics + CEO (email) |
| S1 | A | MALU WILZ Beauté | DE | Managing Director (email verificata Apollo) |
| S3 | A | Unove (Wyatt) | KR | Solo modulo web o LinkedIn |
| S3 | A | Klairs (Wishcompany) | KR | CEO/founder + COO (email verificate Apollo) |
| S3 | A | Mixsoon | KR | Ufficio B2B + Regional B2B Sales |
| S3 | A | Haruharu Wonder | KR | Global Marketing (email verificata) |
| S3 | A | Dr. Ceuracle | KR | Nessun canale ufficiale: **non inviare** |
| S3 | A | KSECRET | KR | Ufficio sales & wholesale |
| S3 | A | Dr. Wu | TW | Director of Product Development (email verificata) |
| S3 | A | Greenvines | TW | Co-founder e COO (email) |
| S3 | A | Mizumi | TH | Ufficio export (nome founder da verificare) |
| S3 | A | Allies of Skin | SG | COO (email verificata) |
| S3 | A | Suu Balm | SG | Regional Product Development Lead (email verificata) |
| S3 | A | Naked Sundays | AU | VP Product Innovation (email verificata) |
| S3 | A | Go-To Skincare | AU | Contatto generale; CEO e R&D su LinkedIn |
| S3 | A | KORA Organics | AU | CEO + Product Development Manager (email) |
| S3 | A | Ultra Violette | AU | Co-founder + R&D Lead (email verificate) |
| S3 | A | Emma Lewisham | NZ | Head of R&D (email verificata) |
| S3 | A | TYPEBEA | AU | CEO (email verificata) |

Aggiunte dalle altre fonti (priorità B da confermare con Eugenio): Breathe B International, Kohonjin Republic, Revcell (Tier 1 nella v2 del repo), ALMADO Inc. (giro stand del 13/11), Epigeneva ed EVENSWISS (scan DE/CH, SWISS Pavilion 2026).

## 5. Lista di lavoro per il segmento 2 (retailer sopra i 500 punti vendita)

Dalla ricerca Drive R2, con le correzioni della sezione 2. Punti vendita: dato della ricerca, da riconfermare prima dell'uso esterno.

| Priorità | Retailer | Paese | Canale | Punti vendita (fonte Drive) |
|---|---|---|---|---|
| A | Watsons (A.S. Watson) | HK / APAC | Health & beauty | Oltre 8.000 Watsons |
| A | MatsukiyoCocokara | JP | Drugstore + farmacia | Oltre 3.400 |
| A | Tsuruha–Welcia | JP | Drugstore | 5.652 (settembre 2026) |
| A | Sundrug | JP | Drugstore | 1.542 (marzo 2025) |
| A | Sugi Pharmacy | JP | Farmacia + drugstore | Oltre 2.000 |
| A | Create SD | JP | Drugstore (format beauty Cremo) | 831 (maggio 2025) |
| A | CJ Olive Young | KR | Health & beauty | Oltre 1.350 (fine 2024) |
| A | Guardian (DFI) | SEA | Health & beauty | Oltre 1.100 |
| A | FPT Long Châu | VN | Farmacia | Oltre 2.000 |
| A | Apollo Pharmacy | IN | Farmacia | Oltre 7.500 |
| A | MedPlus | IN | Farmacia | Oltre 4.500 |
| A | Chemist Warehouse | AU/NZ | Farmacia | Oltre 600 |
| B | MUJI, Don Quijote, AEON, Cosmos Drug, Kusuri no Aoki, Pharmacity, Mercury Drug, Southstar Drug, TerryWhite Chemmart | vari | vari | Da qualificare |
| Escluso | Sasa (159), Mannings HK/Macao, Priceline, Health & Glow | — | — | Sotto i 500 |

## 6. Cosa cambia negli altri file

- `01-idee.md`: il segmento 2 parte da 12 A invece di 4; Mannings esce dal segmento.
- `02-piano-giorno-per-giorno.md`: il 26/10 la lista A non va costruita da zero ma confermata da Eugenio (esclusi e cambi di priorità del file 03).
- `03-template-messaggi.md`: variante B sospesa fino a conferma del time to market.
- `04-domande-aperte.md`: nuove domande D1-D5 e C8.
- L'app `app/radar-clienti.html` contiene la lista di lavoro a livello azienda, senza dati personali.

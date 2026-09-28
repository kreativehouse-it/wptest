# MedGemma Parser: plugin per OJS 3.4

Plugin generico per **Open Journal Systems 3.4.0.x**. Quando un autore completa l'invio, il plugin prende il manoscritto, ne estrae il testo e lo manda a un modello **MedGemma** su un endpoint **AWS SageMaker**. Il risultato compare agli editor in una nuova tab **Analisi AI** nella pagina del workflow.

Cosa estrae:

- **Metadati**: titolo, autori e affiliazioni, autore corrispondente, abstract, parole chiave, tipo di articolo, sezioni, numero di riferimenti, finanziamenti.
- **Dati clinici**: disegno dello studio, popolazione, setting, campione, interventi, comparatori, outcome, follow-up, patologie con ICD-10 suggerito, farmaci, termini MeSH, registrazione del trial, risultati principali.
- **Check pre-review**: linea guida di reporting (CONSORT, PRISMA, STROBE…), checklist con evidenze testuali, approvazione etica, consenso informato, conflitti di interesse, disponibilità dei dati, punti da verificare.

## Come funziona

1. L'autore invia la submission e OJS emette l'evento `SubmissionSubmitted`.
2. Il plugin mette in coda un job (`AnalyzeSubmissionJob`), così l'invio dell'autore non rallenta.
3. Il job sceglie il manoscritto principale tra i file del genere "documento", esclusi supplementari e dipendenti. L'ordine di preferenza è DOCX, ODT, PDF, HTML, TXT.
4. Estrae il testo e chiama SageMaker `InvokeEndpoint` con firma AWS SigV4. Non serve l'SDK AWS.
5. Salva il JSON restituito nella tabella `medgemma_analyses` e lo mostra nella tab.

Journal manager, section editor e amministratori possono rilanciare l'analisi con il pulsante **Rianalizza**. Autori e revisori non vedono la tab.

## Installazione

Serve un account **amministratore del sito** OJS. Se avete solo token API senza accesso admin, chiedete l'installazione al vostro hosting.

1. Create il pacchetto:
   ```bash
   ./build.sh          # genera medgemmaParser.tar.gz (include vendor/ con smalot/pdfparser)
   ```
2. In OJS andate su **Impostazioni › Sito web › Plugin › Carica un nuovo plugin** e caricate `medgemmaParser.tar.gz`. Il plugin crea la tabella da solo.
3. Nella rivista attivate **Analisi manoscritti MedGemma** e aprite **Impostazioni**.

## Configurazione

| Campo | Esempio |
|---|---|
| Regione AWS | `eu-west-1` |
| Nome endpoint | `medgemma-27b-text-it` |
| Access key ID / Secret | credenziali di un utente IAM dedicato |
| Formato richiesta | `messages` per vLLM, TGI Messages API e JumpStart; `inputs` per TGI classico |
| Analisi automatica | attiva l'analisi a ogni nuova submission |
| Caratteri massimi | default 60000; oltre il limite si tengono inizio (75%) e fine (25%) |
| Token di risposta | default 4096 |
| Lingua di sintesi | `Italian` |

La secret key non viene mai rimandata al browser: se lasciate il campo vuoto, resta quella salvata.

Policy IAM minima:

```json
{
  "Version": "2012-10-17",
  "Statement": [{
    "Effect": "Allow",
    "Action": "sagemaker:InvokeEndpoint",
    "Resource": "arn:aws:sagemaker:eu-west-1:ACCOUNT_ID:endpoint/NOME-ENDPOINT"
  }]
}
```

## Coda dei job

OJS 3.4 esegue i job in coda alla fine delle richieste web (`job_runner = On` in `config.inc.php`, attivo di default). Su riviste con poco traffico conviene un cron:

```
* * * * * php /percorso/ojs/lib/pkp/tools/jobs.php run
```

## Limiti da conoscere

- **Timeout SageMaker**: un endpoint real-time deve rispondere entro 60 secondi. Con MedGemma 27B e articoli lunghi il limite può scattare: riducete "Caratteri massimi" e "Token di risposta" oppure usate un'istanza GPU più potente.
- **PDF scansionati** senza livello di testo non si possono leggere e l'analisi va in errore con un messaggio chiaro. Serve l'OCR prima del caricamento.
- **Privacy**: i manoscritti inediti escono da OJS verso il vostro account AWS. Verificate regione, DPA e informativa agli autori.
- I risultati sono **suggerimenti** generati da un modello. La tab lo dichiara e gli editor devono sempre verificarli.

## Struttura

```
MedgemmaParserPlugin.php        registrazione, eventi, tab workflow, impostazioni
MedgemmaParserHandler.php       azione "Rianalizza" (POST + CSRF, solo ruoli editoriali)
MedgemmaParserSettingsForm.php  form impostazioni
classes/SageMakerClient.php     InvokeEndpoint firmato SigV4
classes/TextExtractor.php       PDF, DOCX, ODT, HTML, TXT
classes/PaperAnalyzer.php       scelta file, prompt, parsing JSON
classes/AnalysisRepository.php  tabella medgemma_analyses
classes/MedgemmaSchemaMigration.php
jobs/AnalyzeSubmissionJob.php   job in background
templates/  css/  locale/{it,en}/
```

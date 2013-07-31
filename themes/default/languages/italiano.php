<?php
/**
 * @file /var/www/jeff.git/themes/default/languages/italiano.php
 * @ingroup default_theme localization
 * @brief Contains the italian translations dictionary
 *
 * @author abidibo abidibo@gmail.com
 * @version 0.99
 * @date 2011-2012
 * @copyright Otto srl [MIT License](http://www.opensource.org/licenses/mit-license.php)
 */

return array(

	"JeffDescription" => "<p>Jeff è un framework scritto interamente in php per lo sviluppo di applicazioni web.</p>
<p>Requisiti tecnici:</p>
<ul>
	<li>Server Apache >= 2</li>
	<li>PHP >= 5</li>
	<li>MySql >= 5, oppure altro DBMS dopo aver installato il relativo supporto</li>
</ul>
<p>Jeff è sviluppato implementando il pattern MVC, pertanto è facilmente configurabile e può essere vestito a piacere installando nuovi temi.</p>
<p>Jeff utilizza <a href=\"http://en.wikipedia.org/wiki/HTML5\">html5</a> e <a href=\"http://en.wikipedia.org/wiki/CSS3#CSS_3\">css3</a>, fa largo uso di javascript utilizzando il framework <a href=\"http://www.mootools.net\">mootools</a>, ora alla versione 1.3.</p>
<p>Jeff ha una struttura modulare, pertanto è facilmente estendibile. Si possono creare nuovi plugin o moduli da installare nel sistema.</p>",
	"Login"=>"Login",
	"login"=>"login",
	"poweredby"=>"realizzato da",
	"AdminArea"=>"Area Amministrativa",
	"adminWelcome"=>"<p>Benvenuto nella sezione amministrativa di Jeff. Da qui potrai controllare ogni aspetto della tua applicazione, compatibilmente con i tuoi privilegi.</p>",
	"ManageTable"=>"Gestione tabella",
	"Record"=>"Record",
	"RecordInTable"=>"Record nella tabella",
	"edit"=>"modifica",
	"Edit"=>"Modifica",
	"delete"=>"elimina",
	"insert"=>"inserisci",
	"save"=>"salva",
	"saveContinueEditing"=>"salva e continua la modifica",
	"exportSelected"=>"esporta selezionati",
	"exportAll"=>"esporta tutti",
	"insertNewRecord"=>"inserisci nuovo record",
	"SelectAtleastRecord"=>"Seleziona almeno un record",
	"ProcedeDeleteSelectedRecords"=>"Procedere con l'eliminazione dei record selezionati?",
	"yes"=>"si",
	"no"=>"no",
	"userEditPwdLabel"=>"lasciare il campo vuoto se non si intende modificare la password",
	"ManageGroups"=>"Gestione gruppi",
	"ManageGroupsExp"=>"I 5 gruppi di sistema (<b>SysAdmin</b>, <b>Admin</b>, <b>Power</b>, <b>User</b> e <b>Guest</b>) non possono essere modificati e/o eliminati. Il gruppo <b>SysAdmin</b> è il gruppo dell'amministratore di sistema che possiede tutti i privilegi. Il gruppo <b>Guest</b> è associato all'utente non autenticato. E' possibile definire i privilegi per tutti i gruppi (escluso <b>SysAdmin</b>).",
	"GroupsInSystem"=>"Gruppi presenti nel sistema",
	"NewGroup"=>"Nuovo gruppo",
	"GeneralData"=>"Dati generali",
	"ManagePrivileges"=>"Gestione privilegi",
	"description"=>"descrizione",
	"Description"=>"Descrizione",
	"Name"=>"Nome",
	"Preview"=>"Anteprima",
	"ACTIVE"=>"ATTIVO",
	"activate"=>"attiva",
	"TableContent"=>"Contenuto tabella",
	"ManagePrivilegesExp"=>"Elenco delle funzionalità che possono essere attribuite ai vari gruppi. Il gruppo SysAdmin di default possiede tutti i privilegi.",
	"ManageLayout"=>"Gestione layout",
	"ManageLayoutExp"=>"Lista dei temi caricati nel sistema, attivare quello desiderato",
	"Error"=>"Errore",
	"Hints"=>"Sugerimenti",
	"authError"=>"autenticazione errata",
	"compulsoryFieldsError"=>"compilare tutti i campi obbligatori",
	"Class/File"=>"Classe/File",
	"Function"=>"Funzione",
	"Message"=>"Messaggio",
	"Line"=>"Linea",
	"Attention!"=>"Attenzione!",
	"Home"=>"Home",
	"Configuration"=>"Configurazione",
	"AppPref"=>"Opzioni applicazione",
	"DatetimePref"=>"Opzioni data e ora",
	"Languages"=>"Lingue",
	"Users"=>"Utenti",
	"Groups"=>"Gruppi",
	"Permissions"=>"Permessi",
	"Aspect"=>"Aspetto",
	"Layout"=>"Layout",
	"HomeAdmin"=>"Area Amministrativa",
	"Logout"=>"Logout",
	"all"=>"tutti",
	"none"=>"nessuno",
	"label"=>"etichetta",
	"description"=>"descrizione",
	"privileges"=>"privilegi",
	"CantChargeModuleControllerError"=>"Impossibile caricare il controller del modulo %s, sorgente cercata in %s",
	"NoPrimaryKeyTable"=>"La tabella non ha una chiave primaria definita",
	"CantChargeTemplateError"=>"Impossibile caricare il template %s",
	"CSRFDetectError"=>"Rilevato attacco CSRF o submit del form dall'esterno",
	"Sunday"=>"Domenica",
	"Monday"=>"Lunedì",
	"Tuesday"=>"Martedì",
	"Wednesday"=>"Mercoledì",
	"Thursday"=>"Giovedì",
	"Friday"=>"Venerdì",
	"Saturday"=>"Sabato",
	"January"=>"Gennaio",
	"February"=>"Febbraio",
	"March"=>"Marzo",
	"April"=>"Aprile",
	"May"=>"Maggio",
	"June"=>"Giugno",
	"July"=>"Luglio",
	"August"=>"Agosto",
	"September"=>"Settembre",
	"October"=>"Ottobre",
	"November"=>"Novembre",
	"December"=>"Dicembre",
	"noAvailableOptions"=>"non risultano opzioni disponibili",
	"CantDeleteUploadedFileError"=>"Impossibile eliminare il file caricato",
	"CantUploadError"=>"Impossibile eseguire l'upload del file",
	"chargedFileForm"=>"file caricato: %s",
	"chargedFileFormWithSize"=>"file caricato: %s (%s)",
	"MaxSizeError"=>"Il file supera le dimensioni massime consentite",
	"FileConsistentError"=>"Il file non è conforme alle specifiche",
	"TplNotFound"=>"Impossibile trovare il template, %s",
	"CantChargeTplError"=>"Impossibile caricare il template %s",
	"CantChargeModuleError"=>"Impossibile caricare il modulo %s, sorgente cercata in %s",
	"DefaultTheme"=>"Tema di default",
	"DefaultThemeDescription"=>"Tema base di Jeff",
	"WhiteTheme"=>"Tema chiaro",
	"WhiteThemeDescription"=>"Tema dai colori tenui",
	"CantLoadThemeError"=>"Impossibile caricare la classe del tema %s",
	"SecureCode"=>"Codice di sicurezza",
	"SecureCodeExp"=>"Inserire i caratteri visualizzati nell'immagine",
	"errorCaptcha"=>"Il codice di sicurezza inserito non è corretto",
	"useDotSeparator"=>"usare il punto come separatore dei decimali",
	"duplicateKeyEntryError"=>"il valore '%s' del campo '%s' è già presente",
	"ReservedArea"=>"Area riservata",
	"cantFindPlugin"=>"il plugin %s non è installato",
	"cantFindPluginSource"=>"impossibile trovare il sorgente del plugin %s",
	"Filters"=>"Filtri",
	"FiltersTooltip"=>"Ricerca su campi di testo::vigono le seguenti regole:<br />- <b>valore</b>: cerca campi che contengano valore<br />- <b>&#34;valore</b>: cerca campi che inizino con valore<br />- <b>&#34;valore&#34;</b>: cerca campi esattamente uguali a valore",
	"filter"=>"filtra",
	"reset"=>"azzera",
	"insertValidEmail"=>"inserisci un indirizzo email valido",
	"CannotCloneSingleton"=>"Impossibile clonare un'istanza di singleton",
	"CannotSerializeSingleton"=>"Impossibile serializzare un'istanza di singleton",
	"sameWindow"=>"stessa finestra",
	"newWindow"=>"nuova finestra",
	"startingFromSiteRoot"=>"a partire dalla site root <b>%s</b>",
	"menuGroupsAdminExp"=>"per rendere una voce pubblica non selezionare alcun gruppo, per restringerne la visualizzazione solamente a certi gruppi selezionare quelli desiderati",
	"addSubvoice"=>"aggiungi sottovoce",
	// jeff fields
	"app_title"=>"titolo applicazione",
	"app_description"=>"descrizione applicazione",
	"app_keywords"=>"parole chiave applicazione",
	"session_timeout"=>"durata sessione (s)",
	"date_format"=>"formato data",
	"time_format"=>"formato ora",
	"datetime_format"=>"formato data e ora",
	"language"=>"lingua",
	"code"=>"codice",
	"main"=>"principale",
	"lastname"=>"cognome",
	"firstname"=>"nome",
	"groups"=>"gruppi",
	"phone"=>"telefono",
	"cost"=>"costo orario",
	"category"=>"categoria",
	"class"=>"classe",
	"class_id"=>"identificativo classe",
	"active"=>"attiva",
	"position"=>"posizione",
	"msg404"=>"Il contenuto richiesto non è stato trovato.",
	"title404"=>"404 Pagina non trovata",
	"msg403"=>"Non hai i privilegi per visualizzare il contenuto richiesto.",
	"title403"=>"403 Accesso negato",
  /* NEWS */
	"ManageNews"=>"Gestione news",
	"readAll"=>"leggi tutto",
	"NewsArchive"=>"Archivio news",
	"archive"=>"archivio",
  /* BORROMEO */
	"manageDocCtg"=>"gestione delle categorie di documenti",
	"manageDoc"=>"gestione documenti",
	"BorromeoAdminTitle"=>"Borromeo - Amministrazione",
	"name"=>"nome",
	"documents"=>"documenti",
	"creation_date"=>"creazione",
	"last_edit_date"=>"ultima modifica",
	"ctgs"=>"categorie",
	"title"=>"titolo",
	"tutor_groups"=>"gruppi di tutor",
	"tutor_users"=>"utenti tutor",
	"author_groups"=>"gruppi di autori",
	"author_users"=>"utenti autori",
	"docIndex"=>"Indice",
	"newChapter"=>"nuovo capitolo",
	"newSubchapter"=>"nuovo sottocapitolo",
	"editChapter"=>"modifica capitolo",
	"editSubchapter"=>"modifica sottocapitolo",
	"orderUpdated"=>"ordinamento aggiornato, attendere il ricaricamento della pagina",
	"current"=>"revisione corrente",
	"editText"=>"modifica il testo",
	"addImage"=>"aggiungi immagine",
	"images"=>"immagini",
	"caption"=>"didascalia",
	"file"=>"file",
	"text"=>"testo",
	"image"=>"immagine",
	"mergeAlert"=>"sicuro di voler effettuare il merge della revisione? L'operazione non è reversibile",
	"comment"=>"commento",
	"mergeRevision"=>"merge della revisione",
	"revisionHistory"=>"Storia delle revisioni",
	"noRevisions"=>"non risultano revisioni",
	"revisionNumber"=>"ID revisione",
	"author"=>"autore",
	"lastEditDate"=>"ultima modifica",
	"mergedDate"=>"data merge",
	"mergedAuthor"=>"autore del merge",
	"mergedComment"=>"commento",
	"newRevision"=>"nuova revisione",
	"createNewRevisionFrom"=>"crea nuova revisione a partire da",
	"create"=>"crea",
	"attachment"=>"allegato",
	"attachments"=>"allegati",
	"addAttachment"=>"aggiungi allegato",
	"annotation"=>"annotazione",
	"loadOneOrMoreFiles"=>"<p>Carica uno o più file</p>",
	"annotations"=>"annotazioni",
	"addNote"=>"aggiungi nota",
	"editNote"=>"modifica nota",
	"toggleIndex"=>"apri/chiudi indice",
	"togglePad"=>"apri/chiudi etherpad",
	"docAnnotations"=>"annotazioni",
	"toggleAnnotations"=>"apri/chiudi annotazioni",
	"LoadedFiles"=>"File caricati",
	"addFile"=>"aggiungi file",
	"content"=>"contenuto",
	"publicRevision"=>"revisione pubblica",
	"workRevision"=>"revisione di lavoro",
	"ConfirmDeleteSubchapter"=>"Sicuro di voler eliminare il sottocapitolo? Verranno irrimediabilmente rimossi anche tutti i contenuti, tutte le revisioni e le note.",
	"ConfirmDeleteChapter"=>"Sicuro di voler eliminare il capitolo? Verranno irrimediabilmente rimossi anche tutti i sottocapitoli, i contenuti, tutte le revisioni e le note.",
	"createNewPad"=>"crea un documento real-time",
	"realtimePad"=>"realtime Pad",
	"cantCreateEtherpadGroup"=>"Etherpad, impossibile creare un gruppo",
	"cantCreateEtherpadAuthor"=>"Etherpad, impossibile creare un autore",
	"cantCreateEtherpadPad"=>"Etherpad, impossibile creare un pad",
	"cantDeleteEtherpadPad"=>"Etherpad, impossibile eliminare il pad",
	"cantMergeEtherpadPad"=>"Etherpad, impossibile effettuare il merge del pad",
	"contactServerAdmin"=>"contattare l'amministratore del sistema",
	"noPublishedDocuments"=>"non risultano documenti pubblicati",
	"noChapters"=>"il documento non contiene capitoli",
	"workingRevision"=>"revisione di lavoro",
	"consolidatedRevision"=>"revisione consolidata",
	"clickHereToInsertText"=>"clicca qui per inserire il testo",
	"saveComplete"=>"salvataggio riuscito",
	"continueDeletingRevisionAlert"=>"Sicuro di voler eliminare la revisione? L'operazione non è reversibile.",
	"captchaError"=>"Il codice di sicurezza inserito non è corretto",
	"managePublicAnnotation"=>"gestione annotazioni pubbliche",
	"publicAnnotationFormInformation"=>"Le note inserite verranno moderate prima della pubblicazione.",
	"document"=>"documento",
	"chapter"=>"capitolo",
	"subchapter"=>"sottocapitolo",
	"published"=>"pubblicato",
	"realtimeNotes"=>"note realtime",
)

?>

<?php
declare(strict_types=1);

namespace Tests\Unit\Generator;

use PHPUnit\Framework\TestCase;
use Remp\MailerModule\ContentGenerator\Engine\EngineFactory;
use Remp\MailerModule\Generators\ArticleLocker;
use Remp\MailerModule\Generators\EmbedParser;
use Remp\MailerModule\Generators\NewsfilterGenerator;
use Remp\MailerModule\Generators\WordpressHelpers;
use Remp\MailerModule\PageMeta\ContentInterface;
use Remp\MailerModule\Repository\SourceTemplatesRepository;

class NewsfilterGeneratorTest extends TestCase
{
    private $generator;

    protected function setUp(): void
    {
        $engineFactory = $GLOBALS['container']->getByType(EngineFactory::class);

        $this->generator = new NewsfilterGenerator(
            $this->createMock(SourceTemplatesRepository::class),
            $this->createMock(WordpressHelpers::class),
            $this->createMock(ContentInterface::class),
            $this->createMock(EmbedParser::class),
            $this->createMock(ArticleLocker::class),
            $engineFactory
        );
    }

    public function testPreprocessParameters()
    {
        $wpJson = <<<EOD
{
    "post_authors": [
        {
            "user_url": "http:\/\/localhost\/dnwp\/wordpress\/autor\/ria-gehrerova\/",
            "ID": "663",
            "user_nicename": "ria-gehrerova",
            "user_email": "ria.gehrerova@gmail.com",
            "display_name": "Ria Gehrerová"
        }
    ],
    "post_url": "http:\/\/localhost\/dnwp\/wordpress\/1084435\/matej-sajfa-cifra-mladi-dnes-chcu-byt-za-pol-roka-slavni-mna-si-v-radiu-prve-styri-roky-ani-nevsimli\/",
    "ID": 1084435,
    "post_author": "663",
    "post_date": "2018-04-04 17:49:08",
    "post_date_gmt": "2018-04-04 15:49:08",
    "post_content": "<i>Matej “Sajfa” Cifra v rozhovore hovorí aj o tom:<\/i>\r\n<ul>\r\n \t<li><i>prečo si do relácie zavolal Kisku a prečo žartovali o ovracanom lietadle;<\/i><\/li>\r\n \t<li><i>kedy zavolá Fica a koho určite nepozve;<\/i><\/li>\r\n \t<li><i>či by moderoval aj politický míting;<\/i><\/li>\r\n \t<li><i>ako zarábajú slovenskí youtuberi a či sa majú angažovať pred voľbami;<\/i><\/li>\r\n \t<li><i>a prečo je podľa neho rádio budúcnosť.<\/i><\/li>\r\n<\/ul>\r\n&nbsp;\r\n\r\n<b>Ľudia vás poznajú najmä ako moderátora v rádiu. Kedy ste pochopili, že budúcnosťou sú vlogy?<\/b>\r\n\r\nBol som v situácii, keď som mal pocit, že v rádiu trocha stagnujem, hoci som bol obklopený svojimi kamarátmi, ktorých si vážim a vedia mi povedať, kde mám zabrať. Navyše som zistil, že moje pôsobenie v slovenskom mediálnom priestore je limitované tým, komu sa páčim a komu sa nepáčim. Zvyčajne o tom rozhoduje nejaký generálny alebo programový riaditeľ. Robil som v televízii veľa projektov, ale vždy ma potrebovali zapasovať do takej polohy, v ktorej by som sa páčil šesťdesiatročnej pani, trinásťročnému človeku a aby si aj 35-ročný manažér z Bratislavy povedal, že ten Sajfa je vlastne v pohode. Pôsobilo to na mňa zväzujúco a mal som pocit, že vtedy nie som úplne sám sebou. Preto som hľadal platformu, kde by som taký mohol byť. Na YouTube môže človek robiť, čo chce, ako chce a kedy chce. Ak to, samozrejme, spĺňa nejaké etické kritériá. Zistil som, že tá platforma na Slovensku ostáva nevyužitá pre staršiu cieľovú skupinu, ku ktorej patrím aj ja, a povedal som si, že je to niečo, čo by ma pravdepodobne bavilo.\r\n\r\n<b>Vo svojich vlogoch máte rôznych ľudí – od prezidenta <a href=\"https:\/\/www.youtube.com\/watch?v=9pqFVGYSOKc\" target=\"_blank\" rel=\"noopener\">Andreja Kisku<\/a> po <a href=\"https:\/\/www.youtube.com\/watch?v=ZaIX_yIEQvk\" target=\"_blank\" rel=\"noopener\">Braňa Mojseja<\/a>. So speváčkou <a href=\"https:\/\/www.youtube.com\/watch?v=CO7gAAcZWP8\" target=\"_blank\" rel=\"noopener\">Emmou Drobnou<\/a> ste sa stretli pár dní po tom, čo v televízii tvrdila, že zem je plochá. Vo videách sa zdá, že so všetkými skvelo vychádzate a k nikomu nie ste kritický. Prečo?<\/b>\r\n\r\nPretože k ľuďom pristupujem tak, ako chcem, aby pristupovali ku mne. Nehľadám na nich chyby. To, že chcem vychádzať s každým, neznamená, že nemám pevné jadro. Nemám rád, keď niekto hovorí, že je pod jeho úroveň s niekým hovoriť. Ja to nikdy nepoviem. Aj s Braňom Mojsejom sa budem rozprávať o tom, ako chodí objímať stromy alebo ako bojuje s alkoholom. Tak isto sa budem s rešpektom baviť s Kiskom a s MMA bojovníkom, ktorý má podľa mnohých vymlátený mozog. Dám im priestor vyniknúť a ukázať, akí sú. To je moje životné nastavenie. Hľadám v ľuďoch to dobré a myslím si, že v každom z nás, i keď veľmi hlboko, niečo dobré musí byť.\r\n\r\n[greybox]<strong>Tento rozhovor je súčasťou magazínu Denníka N, ktorý opisuje, ako fungujú médiá. Magazín vďaka podporovateľom zadarmo doručíme do 430 škôl.<\/strong>\r\n\r\n[articlelink id=\"1083233\"] [\/greybox]\r\n\r\n<b>Úplne v každom?<\/b>\r\n\r\nStretol som sa s rôznymi ľuďmi. Stretol som sa aj s Piťom <em>(bratislavský mafiánsky boss Juraj Ondrejčák, momentálne vo väzení, pozn. red.)<\/em> a myslím si, že s ním mám niekde aj fotku, lebo sme sa poznali z mesta. Teraz nehovorím, že je dobrým človekom, ale neodídem z podniku len preto, lebo by tam sedel aj on. A neprestanem chodiť do Steam&amp;Coffee, lebo to vlastnili Kaliňák s Počiatkom.[lock] Svojho času to bola najbližšia kaviareň k rádiu. Ku všetkým ľuďom vo vlogoch pristupujem ako ku kamarátom, lebo viem, že sa mi oveľa viac otvoria, keď zistia, že Sajfa nie je ten arogantný hajzel, čo si o nich myslí, že sú primitívi.\r\n\r\n<b>Ak by vám dali televízie dostatočnú voľnosť, tak by ste sa vlogerom nestali?<\/b>\r\n\r\nTelevízie mi neposkytli dostatočný priestor, kde by som sa mohol sebarealizovať, čo ma na jednej strane veľmi mrzelo a istý čas som sa kvôli tomu aj trápil. Hovorili mi, že som v pohode, ale že so mnou nemôžu riskovať, lebo ak im ten program so mnou nevyjde, tak to môže byť problém. Vtedy som sa rozhodol, že na to, aby o mne existovalo všeobecné povedomie a mohol som moderovať akcie, televíziu nepotrebujem.\r\n\r\n<b>Ako by ste si predstavovali vašu reláciu v televízii?<\/b>\r\n\r\nV minulosti sa mi v televízii najlepšie robila relácia Webmaster. Pozýval som si tam hostí a rozhovory s nimi prekladal zábavnými videami z internetu. Možné však je aj to, že sa do televízie jednoducho nehodím. Internet mi v súčasnosti extrémne vyhovuje.\r\n\r\nV televízii mi ponúkali, aby som šiel robiť niektoré formáty, o ktorých som bol presvedčený, že by som sa do nich nehodil. Spýtali sa ma: A ty nechceš robiť v televízii? A ja som povedal, že nie, nepotrebujem byť v televízii len preto, aby som bol v televízii. Keď príde projekt, ktorý ma osloví, rád sa ho zúčastním. To bolo jedno z najoslobodzujúcejších stretnutí a pocitov, aké som kedy mal. Ten riaditeľ vyzeral, akoby sa s tým ešte nestretol a všetci okamžite siahali po šanciach robiť v televízii, nech je to čokoľvek.\r\n\r\n<b>Viete si predstaviť, že by ste v budúcnosti už robili len vlogy?<\/b>\r\n\r\nJasné. Nehovorím, že to budem robiť doživotne, ale myslím si, že internet len tak ľahko nezanikne a že sa tam v nejakej forme budem vedieť realizovať stále.\r\n\r\n<b>Premýšľate pri nakrúcaní vlogov aj nad tým, čo by mohlo zaujímať dnešných tridsiatnikov alebo štyridsiatnikov?<\/b>\r\n\r\nNie, robím to, čo ma zaujíma, baví a keďže už v médiách pracujem takmer dvadsať rokov, tak viem odhadnúť, čo môže fungovať. Množstvo vecí je však veľmi spontánnych. Napríklad som si kúpil tenisky, ktoré môj známy aj moja žena považovali za ohavné a iným sa, naopak, páčili. Zrazu do toho boli ľudia takí zaangažovaní, že hrozne chceli povedať svoj názor. Je to niečo, čo ich chytilo. Nie je to niečo, čo vyrieši zdravotníctvo. Ale začne to debatu o niečom, o čom ľudia chcú hovoriť.\r\n<p style=\"text-align: center;\"><strong><em>Sajfa vo vládnom špeciáli s prezidentom Andrejom Kiskom<\/em><\/strong><\/p>\r\nhttps:\/\/www.youtube.com\/watch?v=ayWhADwC81g&amp;\r\n\r\n<b>Vo svojich <\/b><a href=\"https:\/\/www.youtube.com\/watch?v=ayWhADwC81g&amp;t=310s\"><b>vlogoch<\/b><\/a><b> aj v relácii <\/b><a href=\"https:\/\/www.youtube.com\/watch?v=9pqFVGYSOKc\"><b>Level Lama<\/b><\/a><b> ste mali prezidenta Kisku. Urobili by ste to isté aj s premiérom Robertom Ficom?<\/b>\r\n\r\nKeď Fico kandidoval na prezidenta, <a href=\"https:\/\/www.youtube.com\/watch?v=5ml0wnfJyzw\">v rádiu som s ním robil rozhovor<\/a>. Pred parlamentnými voľbami som mu pozval pozvánku rovnako, ako iným politickým stranám, ale nakoniec to zrušil. Nemám problém si s ním sadnúť a porozprávať sa s ním, to je to najmenej.\r\n\r\n<b>Zavolali by ste ho aj do svojej relácie Level Lama, kde so známymi ľuďmi hráte počítačové hry a zároveň s nimi robíte rozhovory?<\/b>\r\n\r\nJa by som ho zavolal. A Erik Tomáš by povedal, že určite príde. Je to taká zaujímavá situácia, že sa stavím, že aj vy by ste sa s ním určite vedeli porozprávať. S Kaliňákom sa dá rozprávať do tretej ráno o hocičom. Sú to vzdelaní a inteligentní ľudia, ktorí robia, čo robia. Ťažko, samozrejme, povedať, či s najlepším vedomím a svedomím. Fica si zavolám o pätnásť rokov, keď bude vydávať svoje pamäti.\r\n\r\n<b>Ale Kisku ste si zavolali teraz.<\/b>\r\n\r\nKiska je mi sympatický a má priaznivé vnútorné vyžarovanie. Pri tých ostatných ľuďoch o tom možno trocha pochybujem. Ale tiež ich nesúdim. Nehovorím, že sú zlí ľudia, len asi jednoducho majú iné nastavenie svojho fungovania, svedomia a vedomia.\r\n\r\n[greybox]\r\n\r\n[caption id=\"attachment_1084488\" align=\"alignright\" width=\"200\"]<a href=\"https:\/\/a-static.projektn.sk\/2018\/04\/N37A3338_F-1000x1500.jpg\"><img class=\"wp-image-1084488 size-medium\" src=\"https:\/\/a-static.projektn.sk\/2018\/04\/N37A3338_F-e1522856523713-200x220.jpg\" alt=\"\" width=\"200\" height=\"220\" \/><\/a> Foto - Tomas Thurzo[\/caption]\r\n<h3><i>Matej “Sajfa” Cifra (1979) <\/i><\/h3>\r\n<i>Slovenský moderátor a vloger. Vyštudoval slovenčinu a angličtinu na Filozofickej fakulte Univerzity Komenského v Bratislave. Počas štúdia na vysokej škole začal pracovať v rádiu B1, z ktorého prešiel do Fun Rádia a pôsobí v ňom dodnes v relácii Popoludnie so Sajfom. Moderoval viaceré televízne šou ako napríklad VyVolení alebo Hviezdy na ľade.<\/i>\r\n\r\n[\/greybox]\r\n\r\n<b>Koho by ste si do relácie nezavolali?<\/b>\r\n\r\nAsi ultrapravicovo zmýšľajúcich ľudí, lebo by to zrejme bolo kontraproduktívne. Aj keď by to možno bolo zaujímavé a práve tam by som mal priestor im povedať: Nebláznite, čo máte v tej hlave?\r\n\r\n<b>V jednom z vašich <\/b><a href=\"https:\/\/www.youtube.com\/watch?v=ayWhADwC81g&amp;t=310s\"><b>vlogov žartujete s prezidentom Kiskom<\/b><\/a><b> a jeho tímom o ovracanom lietadle. Začali o tom hovoriť sami alebo ste ich presviedčali?<\/b>\r\n\r\nV tom vlogu nie je žiadna inscenácia. Prezidentovho hovorcu Romana Krpelana som sa spýtal, či v lietadle môžem zapnúť kameru a on mi povedal, že áno. Tak som nakrútil aj to, ako mi začali ukazovať, kde všade to bolo ovracané a ako sa ma prezident pýta, kde mám vedierko. Krpelanovi som ešte pred uverejnením zavolal, že vedia, kedy som mal kameru zapnutú a či sú s tým teda okej. A on znova povedal, že áno. Toto je však jediný raz, keď som niečo dopredu konzultoval. Urobil som to preto, lebo si vážim ten úrad, prezidenta aj Romana. Ak mi niekto povie, nech ho nedávam do vlogu, vždy to akceptujem, lebo si kvôli tomu nechcem kaziť vzťahy.\r\n\r\n<b>Mnohí známi ľudia ako Martin Nikodým robia mítingy pre politické strany. Prijali by ste takú ponuku?<\/b>\r\n\r\nMal som možnosť také niečo robiť, ale vždy som si povedal, že mi to za to nestojí. Znie to asi hrozne, ale také prachy sveta neexistujú. Alebo mi neboli ponúknuté.\r\n\r\n[articlelink id=\"975747\"]\r\n\r\n<b>Za koľko by ste to vzali?<\/b>\r\n\r\nAk by mi Fico povedal, že poďte robiť mítingy a ja vám dám päť triliónov, tak čo ja viem. Ale taká suma je nereálna, čiže to teraz vôbec nie je na stole. Nie som taký magnet, že za mnou idú všetci dôchodcovia, a preto si ma musia zaplatiť. Ale rozumiem Nikodýmovi, rozumiem Kočišovi. Je to pre nich kšeft, tie peniaze si odložia na bankový účet a možno za to neskôr pôjdu s rodinou na dovolenku. Nesúhlasím s tým, čo robia, nie je mi to sympatické, ale rozumiem tomu. Keď mi volali z Progresívneho Slovenska, nech im prídem otvoriť snem, tak som im povedal: Chlapci, sympatizujem s vami, makajte, ale toto pre mňa ešte nie je.\r\n\r\n<b>Máte v zmluve s rádiom napísané, čo môžete a nemôžete robiť popri rádiu?<\/b>\r\n\r\nTú zmluvu som si nečítal, neviem. Ale myslím si, že nie, lebo moje zásadné živobytie je moderovanie eventov a večierkov, kde nemám žiadne obmedzenie. Som „eseročka“, ktorú si rádio najíma na svoje vysielanie, takže som slobodný človek.\r\n\r\nhttps:\/\/www.instagram.com\/p\/BeaLYLuhBBk\/?hl=sk&amp;taken-by=sajfa\r\n\r\n<b>Čím ste najviac? Moderátorom v rádiu, moderátorom súkromných akcií alebo vlogerom?<\/b>\r\n\r\nPredovšetkým som moderátorom rádia. Mám ho hrozne rád a neopúšťam ho, lebo som na ňom vyrástol a poskytuje mi zaujímavý priestor. Keďže vás ľudia nevidia a komunikujete s nimi iba hlasom, má to svoju mágiu, ktorú je ťažké vysvetliť. Keď som bol mladý, nepremýšľal som o tom, že by som chcel byť moderátorom. Chcel som robiť v rádiu. Videl som film, ktorý sa volá „Private parts“ (Súkromné neresti), ktorý je vlastne autobiografiou jedného z najznámejším amerických rozhlasových moderátorov Howarda Sterna. V rádiu pracuje štyridsať rokov a urobil veľkú rádiovú revolúciu. Bol jedným z prvých kontroverzných moderátorov a presne tak som sa o dvadsať rokov videl aj ja. Povedal som si: Ty kokos toto je ono! V rádiu človek môže povedať: Dámy a páni, pekné popoludnie, sedí tu vedľa mňa Robert Fico a chcel by vám niečo povedať. Robo, poď niečo povedať. Nechce sa ti? Okej, asi má toho plné zuby. Človek sa s tým môže hrať. Howard Stern robil podobné veci. Napríklad tvrdil, že s ním v štúdiu sedí Ringo Star, ale nechce hrať na bicie, tak mu ľudia majú zavolať do štúdia, aby ho presvedčili. V sedemdesiatych rokoch chodili do rádia všetci v oblekoch a takéto správanie bolo čudné. Jednoducho bol voľnomyšlienkár.\r\n<p style=\"text-align: center;\"><strong><em>Video: Sajfa v rádiu s Robertom Ficom ako prezidentským kandidátom v roku 2014<\/em><\/strong><\/p>\r\nhttps:\/\/www.youtube.com\/watch?v=5ml0wnfJyzw\r\n\r\n<b>Hlavnými konkurentmi rádií sú dnes streamovacie služby ako Spotify či podcasty. Počúvate ich aj vy?<\/b>\r\n\r\nJasné. Spomedzi médií je rádio stále extrémne silné. V mnohých ohľadoch je dokonca silnejšie ako televízia a rovnako rýchle ako internet. Keď sa niečo stane, rádio s tým vie ísť okamžite von. Nepotrebuje obraz ako televízia ani žiadne ďalšie spracovanie. Som si na sto percent istý, že hlas je budúcnosť. Ľudia sedia v aute a počúvajú rádio, ráno vstanú, počúvajú rádio, vezú deti zo školy, znova počúvajú rádio. Mnohé veci začíname ovládať hlasom. Myslím si, že v rádiu nepotrebujeme hitparády najlepších pesničiek, lebo tie majú naši poslucháči stiahnuté v telefónoch. Rádio má poskytovať niečo viac. Má byť autoritou, ktorá povie, že veci sú takéto a takéto. Alebo poslucháčom ponúka niekoho, koho radi počúvajú a chcú vedieť jeho názor. Možno je to aj budúcnosť správ. Keď si večer zapnem televízne správy, tak už ich nemusím pozerať, lebo presne viem, čo sa počas dňa stalo. Mňa večer zaujíma to, ako si to vysvetliť. Myslím si, že novinárskou úlohou je dať veci do kontextu. Či už je to v rádiu alebo v televízii.\r\n\r\n<b>Takže by sa rádio malo odkloniť od hudby a sústrediť sa viac na diskusiu a vysvetľovanie?<\/b>\r\n\r\nSkôr by si malo dať záležať na tom, aby jeho moderátori boli osobnosťami, ktorí ľuďom vedia niečo priniesť. To znamená, že ich počúvajú, lebo si ich vážia, lebo ich rozosmejú alebo preto, že si povedia svoj názor. Prípadne nech sa rádiá sústredia len na to, čo chcú robiť. Buď nech informujú alebo nech hrajú iba hudbu. Aby od neho každý dostal to, čo naozaj chce.\r\n\r\n<b>Aké médiá počas dňa čítate alebo počúvate?<\/b>\r\n\r\nKeď som v aute, zvyčajne prelaďujem rádiá. Dnes ráno som napríklad počúval Devín, hrali blues. Veľakrát mám pustené Spotify, kde mám viacero obľúbených podcastov o technológiách a internete. Som asi jeden z mála ľudí na Slovensku, ktorí sú aj na Twitteri. Hoci nie aktívne, ale prescrollujem si ho. Facebook mi zvyčajne slúži na to, aby som zhruba vedel, čo ľudia riešia. To však môže byť zradné, lebo človek má na Facebooku kamarátov, to znamená, že žije v bubline. Dnes som bol na Hlavnej stanici nahrávať Sajfov mikrofón, čo je jedna z mojich rubrík, v ktorej sa ľudí pýtam nezmyselné otázky. Dnes som sa ich pýtal: Na východnom Slovensku sa usídlila talianska mafia. Myslíte si, že to súvisí s tým, že východné Slovensko má priamu hranicu s Talianskom? A tí ľudia vzhľadom na to, že vidia mňa a chcú byť za múdrych, tak začnú prikyvovať. My a ani iní novinári často netušíme, čo ľudia riešia. Deväť z desiatich ľudí, ktorí boli na stanici, nevedelo nič o talianskej mafii na východnom Slovensku. V rádiu sme dlho riešili, do akej miery, treba napríklad smrť Jána Kuciaka riešiť, pretože ľudia nevedia, o čo ide. Majú štyristo eur mesačne a je im to jedno. Nehovorím, že tí ľudia sú horší alebo hlúpejší, len majú úplne iné starosti.\r\n\r\n<b>Čo všetko musí vedieť človek, ktorý chce robiť v rádiu? Stačí aby vedel plynule hovoriť?<\/b>\r\n\r\nTo je najbežnejšia predstava, ktorú ľudia majú. Ľudia mi hovoria, že by tiež mohli robiť v rádiu, lebo im strašne ide huba a kamoši im hovoria, že sú vtipní. Tak im hovorím: No a práve ty by si v rádiu nemal robiť. Tam nejde o to, či niekomu mele huba. Nemyslím si, že mne huba mele. Skôr si myslím, že sa treba sústrediť na to, či ten človek má čo povedať alebo nie. Či je jeho názor zaujímavý, vtipný, čudný. Nič nie je horšie ako človek, ktorý robí v médiách a chce sa páčiť všetkým. Bytostne s tým nesúhlasím. Aj ja polarizujem ľudí. Päťdesiat percent ma má rado a päťdesiat percent si myslí, že som idiot. Ale je dobré, ak si väčšina ľudí povie, že aj keď ma nemajú radi, tak ma rešpektujú, lebo nie som blbý. Každého človeka, ktorý ma nemá rád a myslí si, že som idiot, som schopný na osobnom stretnutí presvedčiť, že to tak nie je. Lebo ľudia si o mne v časopise prečítajú, že som bol na dovolenke v Dubaji a povedia si, že som hajzel z Bratislavy. Ale ja som sa pritom narodil do skromných pomerov. Môj otec je polymérny vedec a moja mama je stredoškolská učiteľka. Ľudia si z toho mediálneho obrazu niečo vyberú a podľa toho si ma zaradia. Ak ma ľudia sledujú na sociálnych sieťach, tam ma môžu vidieť takého, aký som.\r\n<p style=\"text-align: center;\"><strong><em>Video: Andrej Danko u Sajfu v rádiu<\/em><\/strong><\/p>\r\nhttps:\/\/www.youtube.com\/watch?v=bZXOXOj48G4&amp;index=16&amp;list=PLGpPhicKtVUR800b7F6girxIbFjG0PRRn\r\n\r\n<b>Aký by teda mal byť človek, ktorý chce robiť v rádiu? Musí zvládať aj zložitú techniku?<\/b>\r\n\r\nKedysi sme s Adelou Banášovou hovorili len do mikrofónu a niekto iný mixoval. Ale keď Adela odišla, musel som sa to naučiť robiť sám. Ovládať techniku sa však za dva mesiace naučí aj cvičená opica, vôbec to nie je ťažké. Ľudia sa ma napríklad pýtajú, akú kameru používam na vlogovanie. A ja im hovorím, že je to jedno, lebo ide o obsah a môže to byť nasnímané aj telefónom. Ak je to obsahovo slabé, môže to byť natočené aj päťtisícovou kamerou. Tak to funguje aj v rádiu. Nemusia mať všetci hlas ako Mišo Hudák, lebo on má geniálny hlas. Oproti nemu mám podľa mňa nepríjemný piskľavý hlas. Ide skôr o to, čo ľuďom hovoríš a či ťa chcú počúvať. Ale áno, ak má niekto priškrtený hlas, tak v rádiu zrejme nebude pracovať. Ani v televízii nepracujú úplne ohavní ľudia.\r\n\r\n<b>Môže človek pracujúci v rádiu šušlať alebo račkovať?<\/b>\r\n\r\nUrčite áno. Môže to byť niečo, podľa čoho, si ho ľudia zapamätajú. Napríklad moderátor Markízy Boris Pršo račkuje ako blázon, ale je obľúbený, lebo vie niečo zaujímavé ponúknuť a je to dobrý človek.\r\n\r\n<b>Dá sa na Slovensku z vlogovania vyžiť?<\/b>\r\n\r\nZáleží na tom, aké máte štandardy. To, čo zarobím v rádiu, miniem na zaplatenie lízingu, nájomného a podobne. Peniaze pre seba si zarobím moderovaním rôznych akcií. Ale to je moje individuálne nastavenie. Ak niekomu stačí tisícka mesačne, môže robiť len YouTube, ak bude mať dostatok fanúšikov. Ak však máte mesačný štandard desaťtisíc eur, tak vám vlogovanie nebude stačiť. Mne YouTube poskytuje peniaze, ktoré ma potešia, ale veľmi ich nevnímam. Raz keď Kočner odniekadiaľ dostal tri milióny, povedal, že si to na účte ani nevšimol, tak podobne je to aj s mojimi peniazmi od YouTube. Aj chalani ako Expl0ited, Slážo alebo Gogo zarábajú oveľa viac peňazí na spoluprácach ako na peniazoch od YouTube. Ak by Gogo, ktorý má 1,6 milióna, odberateľov pôsobil v Amerike, od YouTube by dostával oveľa viac peňazí, ako tu, kde je obmedzený na Slovensko a Česko.\r\n\r\n<b>Selassie spolupracoval s Nadáciou otvorenej spoločnosti na vlogoch z rómskej osady, Gogo zasa robil projekt, v ktorom darovali auto rómskej rodine a tiež mladých ľudí vyzýval voliť. Viete si predstaviť, že by ste do svojich vlogov tiež vložili nejaké podobné posolstvo?<\/b>\r\n\r\nKeď ma niekto osloví, tak s tým zrejme nebudem mať problém. Len to musí byť zaujímavý projekt, ktorý bude mať hlavu a pätu. Ja sám také niečo nevymýšľam, ale som tomu otvorený. Na sto percent viem napríklad povedať, že keď bude pred voľbami, tak budem tiež mladých vyzývať, aby sa na to nevykašľali.\r\n\r\nhttps:\/\/www.instagram.com\/p\/Bat2qJshzMZ\/?hl=sk&amp;taken-by=sajfa\r\n\r\n<b>Vo svojich videách stále viac hovoríte o tom, čo je správne a čo nie. Je to aj preto, že si uvedomujete svoj vplyv na mladých ľudí?<\/b>\r\n\r\nUvedomujem si to, ale vychádza to zo mňa akosi spontáne. Mám 38 rokov a hoci nie som rodičom, už mám čosi osobne aj profesionálne zažité. Môžem teda mladým ľuďom povedať, aby boli trpezliví a aby nemali predstavu, že nič nebudú robiť zadarmo a že do pol roka budú senior manažérom. Pretože takú predstavu má dnes množstvo mladých. A vy ste sa s koňom zrazili? Každý sme niekde začínali a každý sa nejako vyvíjal, to znamená, že trpezlivosť je extrémne dôležitá a nič nejde rýchlo a skratkou. Ani Superstar nie je skratka k večnej sláve, lebo tiež sa z tej šou uchytilo len pár ľudí, ktorí na sebe makali aj po skončení šou. Či už v rádiu alebo kdekoľvek inde treba sledovať dlhodobé ciele a nie to, kam sa dostanem za pol roka. Za pol roka v rádiu som sa nedostal nikam. Štyri roky som tam chodil a ľudia nevedeli ani ako sa volám. Dnes chcú byť mladí ľudia hneď veľké hviezdy a hovoria, že chcú robiť v rádiu, lebo o pol roka chcú byť ako ja a mať dvestotisíc ľudí na Instagrame. To sa však nedá urobiť za dva dni, je to dlhodobá práca. V rádiu ma spočiatku držalo najmä to, že som ako jeden z mála vedel po anglicky. Veľmi veľa vecí som preto prekladal alebo tlmočil. Okrem toho som čítal správy, chodil som na Slovan hlásiť výsledok futbalu, chodil som na tlačovky do Slovenského národného divadla. Až po rokoch som začal skutočne moderovať.\r\n\r\n<b>Myslíte si, že sú mladí ľudia dnes apatickejší, než staršie generácie?<\/b>\r\n\r\nNie, nemyslím si to. A nemám rád, keď niekto hovorí, že mladí ľudia sú stále len na internete a nechodia dostatočne von. Súhlasím s tým, že by deti mali oveľa viac športovať, lebo inak už nikdy nezískame žiadnu olympijskú medailu. Ale to, že sú teraz mladí ľudia oveľa viac pri počítačoch, je jednoducho súčasná situácia a nemôžeme preto techniku démonizovať. Treba povedať, že technológia im zároveň môže pomôcť realizovať sa, nájsť samých seba a tak ďalej.\r\n\r\nAj keď som mal z toho jemné obavy, nedávno som sa zúčastnil Siedmeho neba s Vilom Rozborilom. Mám voči tomu projektu výhrady, ale objavil sa tu jedenásťročný chalan <a href=\"https:\/\/www.youtube.com\/channel\/UCJurOtLRifGtwHUWr9TD9Uw\">Mako SK<\/a>, ktorý je pripútaný na vozík a robí internetové videá. Tak sme ho šli s prezidentom pozrieť. Ten chalan je pripútaný na vozík, nevie si ísť zahrať futbal ani plávať, ale technológia mu umožňuje sa realizovať a byť šťastným a spokojným. Okej, tak deti teraz robia len toto a nechodia von. Je taká doba a o desať, pätnásť rokov to možno bude úplne inak. Môžeme byť radi, že tu nemáme mor, lebo by to bolo oveľa horšie, než to, že deti sedia za počítačom.\r\n<p style=\"text-align: center;\"><strong><em>Video: Sajfa s Makom<\/em><\/strong><\/p>\r\nhttps:\/\/www.youtube.com\/watch?v=uL4kM1p0Fjo\r\n\r\n<b>Je ťažké naučiť sa robiť vlogy?<\/b>\r\n\r\nNaučil som sa to tak, že som to neustále skúšal. Nemyslím si, že to robím na nejakej profesionálnej grafickej úrovni, ale stále sa zdokonaľujem a robím, čo viem. Základom je obsah. A ak je aj technická realizácia lepšia, tak o to lepšie sa to potom pozerá. Naučil som sa to sám aj na staré kolená. Ak mi niekto povie, že by to s videami skúsil, ale nevie strihať, tak mu na to poviem, nech sa na to teda vykašle. Všetko sa dá naučiť. Prvý vlog mi trvalo zostrihať šesť hodín a teraz mi to trvá tri hodiny, niekedy to stihnem aj za dve. Nejaký čas tomu musím obetovať. Zatiaľ nechcem, aby to robil niekto namiesto mňa, pretože ma to baví.\r\n\r\n<b>Robíte si videá úplne sám?<\/b>\r\n\r\nMám v rádiu kolegu Borisa, ktorý zo 180 vlogov postrihal asi tri, lebo sa to týkalo mojich narodenín, ktoré pre mňa boli prekvapením. Niekedy sa s ním poradím, ale v podstate to robím celé ja.\r\n\r\n<b>Vo vlogoch používate aj drony. Na Slovensku sú veľmi prísne pravidlá, ktoré ich takmer nikde a na nič nepovoľujú používať. Máte ich povolené?<\/b>\r\n\r\nVšetky papiere mám vybavené, ale nie pre Slovensko. Tu nemám nič.\r\n\r\n[articlelink id=\"788231\"]\r\n\r\n<b>Nebojíte sa, že kvôli tomu môžete mať problémy?<\/b>\r\n\r\nViete si predstaviť, že by ma zatkla polícia? Veď ja zavolám Kaliňákovi, že Robo neblázni. (smiech) Rozumiem tomu postoju, ale otvorene priznávam, že nemám pilotné ani letecké skúšky. Mohli by za mnou prísť policajti a odviesť ma v putách. Keď to spravia, tak to možno bude zaujímavý obsah do vlogu. Nikdy s dronom nelietam nad štadiónom plným ľudí a vždy ho mám v dohľade. Viem, že tu nejaké legislatíva existuje a som si vedomý toho, že ju zrejme porušujem.\r\n\r\nhttps:\/\/www.instagram.com\/p\/BVxYnqihZAd\/?hl=sk&amp;taken-by=sajfa\r\n\r\n<b>Pred rokom ste hovorili, že za post na Instagrame si vypýtate asi päťsto eur. Teraz máte oveľa viac followerov. Zmenila sa odvtedy cena?<\/b>\r\n\r\nCena závisí od toho, aký je to klient a čo odo mňa chce. O tých päťsto eur mi nejde, zarobím si ich aj niekde inde. Musí to byť niečo, čo mi dáva zmysel a obohacuje ma to a potom si za to rád vypýtam peniaze. Ale ten pomer vecí, ktoré som vzal a ktoré som poodmietal je asi jedna k deviatim. Radšej si vyberiem dlhodobejšiu spoluprácu, než človeka, ktorý za mnou príde s turbovysávačom a žiada ma, nech sa s ním odfotím. Mladí ľudia dnes vedia veľmi dobre odhadnúť, čo je prirodzené a čo nie. Je potom na mojej šikovnosti, či im viem čosi zaplatené zaobaliť do prirodzena.\r\n\r\n<b>Vo svojich vlogoch často vystupujete so svojou ženou. Stanovili ste si v súkromí nejaké hranice, za ktoré s kamerou nejdete?<\/b>\r\n\r\nObaja robíme v médiách a obaja podvedome vieme, kde je tá hranica. Nikdy sme sa nedohodli, že ľuďom nikdy neukážeme svoju kúpeľňu alebo posteľ. Prirozdene vieme, čo sa do toho vlogu dá použiť a čo nie. Keď sa pohádame, nedáme to do vlogu, lebo je to zbytočné. Niektorí mi hovoria, že tými vlogmi odhaľujem veľmi veľa súkromia. Ale nerozumiem, čo zásadné som zo svojho súkromia odhalil. Nechodím s tou kamerou stále a nenakrúcam všetko. Z dvoch alebo troch dní urobím dvanásťminútové video. A ak niekto nechce byť točený, tak to rešpektujem. Ale nestáva sa mi často, že by niekto nechcel. Skôr sa ma pýtajú, prečo so sebou nemám kameru. Nechodím s ňou napríklad na eventy, ktoré moderujem, lebo mi dodatočnú reklamu vo vlogu nezaplatili.\r\n\r\n<b>V jednom vlogu ste omylom uverejnili číslo na Goga a jemu potom celý deň vyvolávali nadšené deti. Za túto chybu ste sa mu potom ospravedlnili. Je ešte niečo, čo ste po zverejnení videa oľutovali?<\/b>\r\n\r\nRaz som nechtiac uverejnil aj svoje číslo, lebo mi kuriér čosi priniesol a keď som tú zásielku podpisoval, nakrútil som aj svoje telefónne číslo. V mobile mám aj kvôli tomu uložených asi 150 telefónnych čísel ako „Nedvíhať“.",
    "post_title": "Matej Sajfa Cifra: Mladí dnes chcú byť za pol roka slávni, mňa si v rádiu prvé štyri roky ani nevšimli",
    "post_excerpt": "Matej Cifra sa nedohodol s televíziami a tak prešiel na internet. Mladí ho dnes nepoznajú z rádia, ale z YouTubu, kde má už viac ako 140-tisíc odberateľov.",
    "post_status": "publish",
    "post_name": "matej-sajfa-cifra-mladi-dnes-chcu-byt-za-pol-roka-slavni-mna-si-v-radiu-prve-styri-roky-ani-nevsimli",
    "post_modified": "2018-04-04 17:52:54",
    "post_modified_gmt": "2018-04-04 15:52:54",
    "post_type": "post",
    "post_tags": [],
    "post_categories": [
        {
            "term_url": "http:\/\/localhost\/dnwp\/wordpress\/slovensko\/",
            "term_id": 430,
            "name": "Slovensko",
            "slug": "slovensko"
        }
    ]
}
EOD;
        $wpJson = str_replace(["\n", "\r", "\t"], ["", "", ""], $wpJson);

        $data = json_decode($wpJson);

        $output = $this->generator->preprocessParameters($data);
        self::assertEquals("Matej Sajfa Cifra: Mladí dnes chcú byť za pol roka slávni, mňa si v rádiu prvé štyri roky ani nevšimli", $output->title);
        self::assertEquals("Ria Gehrerová", $output->editor);
        self::assertEquals("http://localhost/dnwp/wordpress/1084435/matej-sajfa-cifra-mladi-dnes-chcu-byt-za-pol-roka-slavni-mna-si-v-radiu-prve-styri-roky-ani-nevsimli/", $output->url);
        self::assertStringContainsString("Matej Cifra sa nedohodol s televíziami a tak", $output->summary);
        self::assertStringContainsString("ako zarábajú slovenskí youtuberi", $output->newsfilter_html);
    }
}

-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия сервера:               5.7.19 - MySQL Community Server (GPL)
-- Операционная система:         Win64
-- HeidiSQL Версия:              9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры для таблица cms.adminNote
DROP TABLE IF EXISTS `adminNote`;
CREATE TABLE IF NOT EXISTS `adminNote` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `groupView` tinyint(3) unsigned NOT NULL DEFAULT '255',
  `groupEdit` tinyint(3) unsigned NOT NULL DEFAULT '255',
  `title` varchar(300) NOT NULL,
  `html` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.adminNote: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `adminNote` DISABLE KEYS */;
INSERT INTO `adminNote` (`id`, `groupView`, `groupEdit`, `title`, `html`) VALUES
	(2, 255, 255, 'Рекомендации по редактированию статей (редактор CKEditor)', 'В админке вашего сайта для редактирования текста используется популярный редактор CKEditor (<a href="http://ckeditor.com/">http://ckeditor.com</a>), внешне он похож на популярную программы Word. К сожалению визуальные редакторы далеки от совершенства и ниже представлены несколько замечаний, которые позволят избежать ошибок.\r\n1. На страницах вашего сайта, для любого текста задано определённое форматирование (название шрифта, цвет, размер, отступы от краёв, выравнивание и т.д.), однако в админке, при редактировании текста, это форматирование не действует, поэтому стиль текста может отличаться. Не нужно специально настраивать стиль текста в админке, за исключением случаев, когда вы сознательно хотите выделить стиль текста.\r\n2. Клавиша <b>Enter</b> - новый абзац, а сочетание клавиш <b>Ctrl + Enter</b> - переход на новую строку. Как правило каждый абзац текста выделяется бОльшими отступами, чем отдельные строки текста.\r\n3. Если вы загружаете на сайт фотографии через визуальный редактор, то помните, что в имени файла не должно быть пробелов и русских букв.\r\n4. Если вы с другого сайта копируете текст, содержащий картинки, то сами картинки не копируются, они физически остаются на том сайте, с которого копируется текст. Чтобы картинка находилась на вашем сайте, необходимо сначала сохранить изображение на свой компьютер, затем загрузить на сайт и вставить в текст.\r\n5. Старайтесь избегать копирования текста из Word или с других сайтов через буфер обмена. Это приводит к тому, что копируется также и форматирование текста, которое, конечно же, отличается от принятого на вашем сайте, поэтому текст может выглядеть неуклюже. Кроме того, вы также копируете много скрытого кода - его не видно на сайте, однако он может в пять раз превышать размер самого текста - это увеличивает скорость загрузки страницы, а также осложняет работу роботам поисковых систем. Используйте кнопку "вставить из Word" (форматирование не удаляется полностью, однако из него удаляется много явно ненужного), а ещё лучше - "вставить только текст".\r\n6. При написании достаточно большого текста используйте кнопку "развернуть", чтобы растянуть область редактора на весь экран. Однако <b>обязательно</b> периодически сохраняйте набранный текст, т.к. его легко потерять случайным нажатием не той клавиши или случайным закрытием окна браузера.\r\n7. Чтобы вставить какой-либо HTML-код (например счётчик или комментарии ВКонтакте) или если вы хотите увидеть HTML-разметку страницы, используйте кнопку "источник".');
/*!40000 ALTER TABLE `adminNote` ENABLE KEYS */;

-- Дамп структуры для таблица cms.articleCategory_en
DROP TABLE IF EXISTS `articleCategory_en`;
CREATE TABLE IF NOT EXISTS `articleCategory_en` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` smallint(5) unsigned NOT NULL,
  `title` varchar(35) NOT NULL,
  `metaTitle` varchar(50) DEFAULT NULL,
  `metaKeyword` varchar(255) DEFAULT NULL,
  `metaDescription` varchar(255) DEFAULT NULL,
  `alias` char(35) NOT NULL,
  `text1` mediumtext,
  `text2` mediumtext,
  `onPage` tinyint(3) unsigned NOT NULL DEFAULT '20',
  PRIMARY KEY (`id`),
  KEY `parentId` (`parentId`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.articleCategory_en: 2 rows
/*!40000 ALTER TABLE `articleCategory_en` DISABLE KEYS */;
INSERT INTO `articleCategory_en` (`id`, `parentId`, `title`, `metaTitle`, `metaKeyword`, `metaDescription`, `alias`, `text1`, `text2`, `onPage`) VALUES
	(1, 0, 'News', '', '', '', 'news', '<p>\r\n	Dear readers, we offer you a series of photographs taken in RP Buturlino Nizhny Novgorod region, where there was a collapse of the soil...</p>\r\n<p>\r\n	 </p>\r\n', NULL, 20),
	(2, 0, 'Articles', '', '', '', 'article', '<div>\r\n	Article - a genre of journalism, in which the author sets the task to analyze the social situation, processes, phenomena primarily in terms of the laws that underpin them.</div>\r\n<div>\r\n	Such genre, the article, characterized by the breadth of theoretical and practical generalization, a deep analysis of the facts and events, a clear social orientation. [Citation 1258 days] The author considers the individual situation, as part of a broader phenomenon. The author argues, and builds its position through the facts.</div>\r\n<div>\r\n	The article expressed deployed thorough argumentative concept of the author or editor about current sociological issues. Also, in the article the journalist must interpret the facts (which may be numbers, additional information that will properly highlight key points and clearly reveal the essence of the question).</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Content</div>\r\n', NULL, 20);
/*!40000 ALTER TABLE `articleCategory_en` ENABLE KEYS */;

-- Дамп структуры для таблица cms.articleCategory_ru
DROP TABLE IF EXISTS `articleCategory_ru`;
CREATE TABLE IF NOT EXISTS `articleCategory_ru` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` smallint(5) unsigned NOT NULL,
  `title` varchar(35) NOT NULL,
  `metaTitle` varchar(50) DEFAULT NULL,
  `metaKeyword` varchar(255) DEFAULT NULL,
  `metaDescription` varchar(255) DEFAULT NULL,
  `alias` char(35) NOT NULL,
  `text1` mediumtext,
  `text2` mediumtext,
  `onPage` tinyint(3) unsigned NOT NULL DEFAULT '20',
  PRIMARY KEY (`id`),
  KEY `parentId` (`parentId`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.articleCategory_ru: 2 rows
/*!40000 ALTER TABLE `articleCategory_ru` DISABLE KEYS */;
INSERT INTO `articleCategory_ru` (`id`, `parentId`, `title`, `metaTitle`, `metaKeyword`, `metaDescription`, `alias`, `text1`, `text2`, `onPage`) VALUES
	(1, 0, 'Новости', '', '', '', 'news', '<p>\r\n	Уважаемые читатели, предлагаем вашему вниманию серию фотографий, сделанных в р.п. Бутурлино Нижегородской области, где произошёл обвал грунта.…</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n', NULL, 20),
	(2, 0, 'Статьи', '', '', '', 'article', '<p>\r\n	Статья́&nbsp;— это жанр&nbsp;журналистики, в котором автор ставит задачу проанализировать общественные ситуации, процессы, явления прежде всего с точки зрения закономерностей, лежащих в их основе.</p>\r\n<p>\r\n	Такому жанру, как статья, присуща широта теоретических и практических обобщений, глубокий анализ фактов и явлений, четкая социальная направленность.[источник&nbsp;не&nbsp;указан&nbsp;1258&nbsp;дней]&nbsp;В статье автор рассматривает отдельные ситуации, как часть более широкого явления. Автор аргументирует и выстраивает свою позицию через систему фактов.</p>\r\n<p>\r\n	В статье выражается развернутая обстоятельная аргументированная концепция автора или редакции по поводу актуальной социологической проблемы. Так же, в статье журналист обязательно должен интерпретировать факты (это могут быть цифры, дополнительная информация, которая будет правильно расставлять акценты и ярко раскрывать суть вопроса).</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<u><strong>Содержание</strong></u></p>\r\n', NULL, 20);
/*!40000 ALTER TABLE `articleCategory_ru` ENABLE KEYS */;

-- Дамп структуры для таблица cms.article_en
DROP TABLE IF EXISTS `article_en`;
CREATE TABLE IF NOT EXISTS `article_en` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `alias` char(56) NOT NULL,
  `title` char(150) NOT NULL,
  `text1` mediumtext,
  `text2` longtext NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `metaTitle` varchar(255) DEFAULT NULL,
  `metaKeyword` varchar(255) DEFAULT NULL,
  `metaDescription` varchar(255) DEFAULT NULL,
  `date` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoryId` (`categoryId`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.article_en: 9 rows
/*!40000 ALTER TABLE `article_en` DISABLE KEYS */;
INSERT INTO `article_en` (`id`, `categoryId`, `alias`, `title`, `text1`, `text2`, `sort`, `metaTitle`, `metaKeyword`, `metaDescription`, `date`) VALUES
	(1, 0, 'index', 'Main page article', NULL, '<p>\r\n	Main page is the information that appears to the user when moving it to the address of the site. In other words, the home page - this is the first thing a visitor encounters, appearing on the site. This rule are subject to all of the sites on the Internet - content providers, fashionable online shopping portals and powerful crowded forums. Purpose of the main page of any site - is the provision of such "acceptance" visitor that, ideally, he became a customer. Or at least to delay the site for a long time.</p>\r\n', 0, 'meta Main page', 'meta Keywords', 'meta Description', NULL),
	(2, 0, 'about', 'About us', NULL, '<p>\r\n	The section "About us" is extremely important for a corporate site or online store, and if used properly can boost sales. People are increasingly interested in products and services, not only in terms of their usefulness. They want to purchase goods and services from companies with history and meaning. They want to know more about who they are buying, and the burden of informing them about this often falls all on the same page. As for online stores, they often do not attach importance to the pages "About Us", while their role in online shopping is growing rapidly.</p>\r\n', 0, '', '', '', NULL),
	(3, 1, '130412', 'Mikhail Babich', '<p>\r\n	April 13, 2013 in Nizhny Novgorod Health Minister Veronika Skvortsova and plenipotentiary representative of the Russian President in the Volga Federal District Mikhail Babich will hold a meeting on the implementation of the activities of regional programs for modernizing healthcare PFD. It is reported by the press service of the presidential envoy in the Volga Federal District</p>\r\n', '<p>\r\n	13 апреля 2013 года в Нижнем Новгороде министр здравоохранения РФ Вероника Скворцова и полномочный представитель Президента России в ПФО Михаил Бабич проведут совещание по реализации мероприятий региональных программ модернизации здравоохранения субъектов ПФО. Об этом сообщает пресс-служба полномочного представителя президента РФ в ПФО,</p>\r\n<p>\r\n	Выездное совещание с участием главы Минздрава РФ организовано по инициативе приволжского полпреда, такое мероприятие в стране проходит впервые.</p>\r\n<p>\r\n	Руководители органов исполнительной власти в сфере здравоохранения из всех субъектов округа будут защищать программы развития здравоохранения своих регионов до 2020 года. Целевые показатели этой работы заложены в майском Указе Президента РФ №598 и предусматривают: повышение эффективности оказания медицинской помощи, увеличение продолжительности жизни россиян, снижение заболеваемости и смертности населения от наиболее значимых заболеваний путем обеспечения доступности качественной медицинской помощи каждому гражданину страны, а также улучшение состояния региональной инфраструктуры учреждений здравоохранения.</p>\r\n<p>\r\n	Уровень проработки региональных программ развития здравоохранения ПФО лично оценят министр здравоохранения РФ и приволжский полпред. Подобный формат совещания позволит регионам ПФО максимально тщательно проработать свои программы, которые должны быть окончательно утверждены до 1 мая 2013 г., а округу в целом подойти к реализации указа Президента РФ более системно.</p>\r\n', 0, '', '', '', NULL),
	(4, 1, '130411', 'I personally always ready to take any of Nizhny Novgorod, which comes with a sensible proposal - V.Hohlov', '<p>\r\n	We are always ready for dialogue with representatives of public organizations, as well as philanthropists or private companies interested in the preservation of historical and architectural heritage. The main thing that the dialogue was constructive, aimed at solving problems.</p>\r\n', '<p>\r\n	"Мы всегда готовы к диалогу с представителями общественных организаций, равно как и меценатами или частными компаниями, заинтересованными в сохранении историко-архитектурного наследия. Главное, чтобы этот диалог был конструктивным, направленным на решение проблемы. При управлении государственной охраны объектов культурного наследия действует научно-экспертный совет, где собираются наиболее авторитетные эксперты, чтобы дать свои рекомендации. Я лично всегда готов принять любого нижегородца, который приходит с дельным предложением", - заявил журналистам Владимир Хохлов, комментируя итоги работы по сохранению историко-архитектурного наследия Нижегородской области. "Как показывает практика, привлечение частных инвесторов к реставрации памятников архитектуры оправдывает себя. Многие памятники были удачно приспособлены под современное использование, принося доход владельцам, - и при этом оставаясь украшением города.</p>\r\n<p>\r\n	Конечно, действия собственников объектов культурного наследия необходимо строго контролировать. Случаи, когда собственник памятника самовольно его разрушал, в Нижегородской области единичны, но, к моему великому сожалению, такое бывало. Главное: мы не позволяем сделать на месте разрушенного памятника какой-то новый объект – наоборот, понуждаем собственника к восстановлению старинного здания по сохранившимся документам. Это наша принципиальная позиция", - сказал В.Хохлов.</p>\r\n<p>\r\n	"Например, здание № 24 по ул. Новой в Нижнем Новгороде. По данному дому был разработан проект реставрации и приспособления для современного использования. Однако, памятник застройщиком был полностью разобран. По инициативе управления и при содействии прокуратуры Нижнего Новгорода, застройщика обязали воссоздать дом № 24 по ул. Новой. Восстановление здания в настоящее время успешно завершено", - подчеркнул В.Хохлов.</p>\r\n<p>\r\n	Напомним, в конце 2012 года губернатор Валерий Шанцев в своем блоге в «Живом Журнале» сообщил, что, по решению нижегородских блогеров и экспертов, 2013 год в регионе объявлен Годом национального культурного наследия. Валерий Шанцев подчеркнул, что история подарила Нижегородской области очень богатое наследие, и за последние годы сделано немало для его сохранения. «Восстановлено около 300 памятников, в том числе, Зачатская башня, дом Рукавишниковых, дом Бугровых, Сироткина, Шуховская башня… Это наследие, доставшееся от наших предков, которое мы должны ценить и беречь», - написал губернатор. Глава региона напомнил, что в 2012 году область отметила 400-летие народного ополчения – значимый исторический праздник, напоминающий о роли каждого поколения в истории родной страны.</p>\r\n', 0, '', '', '', 1365624000),
	(5, 1, '13-410', 'Cemetery', '<p>\r\n	According to a decree signed by the head of the Administration of Nizhny Novgorod, Oleg Kondrashov, changes in the timing of closing the municipal cemetery "New Striginskoe." Informs the Media Relations Administration of Nizhny Novgorod. A ban on new burial in the churchyard rescheduled for October 15, 2013.</p>\r\n', '<p>\r\n	 </p>\r\n<p>\r\n	Согласно постановлению, которое подписал глава администрации Нижнего Новгорода Олег Кондрашов, внесены изменения в сроки закрытия муниципального кладбища «Новое Стригинское». Об этом сообщает управление по работе со СМИ администрации Нижнего Новгорода. Введение запрета на новые захоронения на погосте перенесено на 15 октября 2013 года.</p>\r\n<p>\r\n	Напомним, постановление администрации Нижнего Новгорода «О закрытии муниципального кладбища «Новое Стригинское» было принято 13 марта 2012 года. Ранее сроки введения запрета на осуществление новых захоронений уже переносились на 15 мая и 15 ноября 2012 года, а также на 15 апреля 2013 года.</p>\r\n<p>\r\n	Сроки изменены в связи с наличием свободных мест для осуществления новых захоронений на существующей территории кладбища.<br />\r\n	«Мы понимаем, что прежде, чем закрывать то или иное кладбище для новых захоронений, необходимо предоставлять нижегородцам альтернативные места. Поэтому в настоящее время осуществляется подготовка и оформление документов на ввод в эксплуатацию новой территории кладбища «Новое Стригинское». Мы стараемся, как можно раньше завершить эту процедуру, чтобы погост продолжал функционировать без неудобств для горожан», - рассказал начальник управления по благоустройству Виталий Ковалев.</p>\r\n', 0, '', '', '', 1365537600),
	(6, 2, 'article-1', 'Terms creativity by Milton Glaser', NULL, '<div>\r\n	Milton Glaser (Milton Glazer) - One of the most famous designers of the USA, the artist who created a huge number of masterpieces of graphic design, including the famous logo I ♥ NY. In addition to practice, 80-year-old Glazer deals and theory: lectures at universities, wrote essays and theoretical arguments about the relationship of design and art and their impact on each other, about the profession of the designer and the complexities of the creative process.</div>\r\n<div>\r\n	His speech «Ten Things I Have Learned» (10 things I\'ve learned), which we present to you, has become a landmark in the world of art and sold on the quote.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Work only with those who you like.</div>\r\n<div>\r\n	It took me a lot of time trying to articulate for themselves rule. When I started, I thought the contrary, that the professionalism - is the ability to work with any customers. But over the years it became clear that the most intelligent and good design was conceived for those customers who later became my friends. It seems that the sympathy of our work is much more important than professionalism.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	If you have a choice, do not get a job.</div>\r\n<div>\r\n	I once heard an interview with a remarkable composer and philosopher John Cage. He was then 75. The host asked: "How to prepare for old age?". I always remember the answer Cage: "I have one piece of advice. Do not go to work. If lifelong every day you go to the office, once you put out the door and sent into retirement. And then you certainly will not be ready for old age - it catches you by surprise. Look at me. With 12 years every day I do the same thing: I wake up and think about how to earn a piece of bread. Life has not changed since I became an old man. "</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Avoid unpleasant people.</div>\r\n<div>\r\n	This is a continuation of the first rule. In the 60s he worked Gestalt therapist Fritz Perls called. He suggested that in all respects people either poison the lives of each other, or feed her. This all does not depend on a person, namely the relationship. You know, there\'s an easy way to check the effect on your communication with someone. Spend with this person for a while, dine, stroll or have a drink with a glass of something. And pay attention to how you feel after this meeting - enthusiastic or tired. And all will become clear. Always use this test.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Experience is not enough.</div>\r\n<div>\r\n	I have already said that in his youth he was obsessed with professionalism. In fact, experience - this is another limitation. If you need a surgeon, you are likely to refer to someone who did exactly this operation many times. You do not need someone who is going to invent a new way to use a scalpel. No, no, no experiments, please be careful as you have done and how it has always done before you. In our work all the way around - good one who does not repeat for themselves and others. A good designer wants to use each time a new scalpel or even decide to use a scalpel instead of a garden watering can.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Smaller is not always better.</div>\r\n<div>\r\n	We have all heard the expression "less, but better." This is not quite true. One fine morning I woke up and realized that it was probably nonsense. It maybe sounds good, but in our area this paradox makes no sense. Think of Persian carpets, except in the case of "less" would be better? The same can be said about the work of Gaudi\'s Art Nouveau style and a lot else. So I formulated a new rule for myself: better when just as much as you need.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Lifestyle changes the way of thinking.</div>\r\n<div>\r\n	Brain - the most sensitive organ in our body. It is most subject to change and regeneration. A few years ago in the newspapers flashed a curious story about the search for perfect pitch. As it is known, it is rare even among musicians. I do not know how, but scientists have found that people with perfect pitch another brain structure - somehow distorted some shares. It\'s interesting. But then they found even more interesting thing - if the children 4-5 years old to learn to play the violin, there is a possibility that any of them will begin to change the structure of the cerebral cortex and develop absolute pitch. I am confident that drawing affects the brain as much as music lessons. But instead of hearing we develop attention. The man who paints, draws attention to what is happening around him - but it\'s not as easy as it seems.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Doubt is better than confidence.</div>\r\n<div>\r\n	Everyone talks about how important confidence in what you\'re doing. My teacher of yoga once said: "If you think you have achieved enlightenment, you just ran into a framework of their own." This is true not only in the spiritual practice, but also in the work. For me, it formed the ideological position - are questionable, because perfection - it is an endless development. I get nervous when someone believes in something categorically. It is necessary to question everything. Designers this problem appears even in art school, where we were taught the theory of the avant-garde, and the fact that a person can change the world. In a sense, this is true, but most of the big problems with the end of the creative self-esteem. Commercial cooperation with someone else - it\'s always a compromise, and that in any case it is impossible to resist. You should always allow for the possibility that your opponent may be right. A couple of years ago I read Iris Murdoch is the best definition of love: "love - it is very difficult to realize that someone besides us is real." It\'s brilliant, absolutely any and describes the coexistence of two people.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	It does not matter.</div>\r\n<div>\r\n	One birthday I was given a book by Roger Rosenblatta "Old is beautiful." The name I did not like, but the content is interesting. The very first rule, proposed by the author, was the best. It reads: "It does not matter." No matter what you think - follow this rule, and it will add decades to your life. Whether you are late or come in time, then you or there, or whether you have kept silent say you are smart or stupid. No matter what your hair and how you look at your friends. Whether you will raise at work, whether you buy a new house, you will get a Nobel Prize - it does not matter. Here is wisdom!</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Do not trust styles.</div>\r\n<div>\r\n	Style - concept irrelevant. It is foolish to give ourselves any one style, because none of them do not deserve you entirely. For designers of the old school it was always a problem, because over the years produced handwriting and visual vocabulary. But fashion styles mediated economic indicators, that is almost like a math - it will not argue. This was written by Marx. Therefore, every ten years, is replaced by a stylistic paradigm, and things begin to look different. The same thing happens with fonts and graphics. If you plan to survive in the profession more than a decade, I highly recommend to be prepared for this change.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Tell the truth.</div>\r\n<div>\r\n	Sometimes I think that the design - not the best place to search for the truth. Certainly in our community there is a certain ethic that seems in some cases even written. But it is not a word about the relationship of the designer with the company. They say that 50 years ago all that was sold with the label "beef" was actually chicken. Since then, I was tormented by the question of what then was sold as chicken? Designers are no less responsibility than butchers. Therefore, we should be attentive to the fact that they sell to people under the guise of veal. Doctors have an oath "Do No Harm." I believe that designers should also have its own oath - "Tell the truth."</div>\r\n', 0, '', '', '', 1365710400),
	(7, 2, 'article-2', 'The art on the verge', NULL, '<p>\r\n	Ребята, ничего личного, но&nbsp;очень часто эти слова как нельзя лучше характеризуют творчество представителей этого самого современного искусства. В&nbsp;наш век информации и&nbsp;вседоступности удивить, впечатлить и&nbsp;заставить о&nbsp;себе говорить искушенную публику&nbsp;— дорогого стоит, а&nbsp;потому современные художники, пусть и&nbsp;не&nbsp;все, в&nbsp;погоне за&nbsp;популярностью занялись элементарным эпатажем. Не&nbsp;можешь отрисовать гениально и&nbsp;чтоб мурашки по&nbsp;коже, разденься до&nbsp;гола и&nbsp;просиди в&nbsp;каком-нибудь бункере под пристальными взглядами журналистов. И&nbsp;можно даже ничего не&nbsp;объяснять: люди сами придумают концепцию твоего поведения, и&nbsp;будут рассказывать об&nbsp;этом благоговейным шепотом.</p>\r\n<p>\r\n	Все-таки есть грань, отделяющая настоящее искусство от&nbsp;того, что создано с&nbsp;целью прославиться. В&nbsp;этом материале мы&nbsp;решили собрать неоднозначные объекты современного искусства, которые находятся на&nbsp;этой грани, а&nbsp;к&nbsp;чему они относятся&nbsp;— решите для себя сами.</p>\r\n<h3>\r\n	 </h3>\r\n<h3>\r\n	Мэтью Барни, видеокадр из&nbsp;перформанса «Кремастер-1»</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7585905/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7585905-R3L8T8D-600-z_a29ec5bd.jpg" /></a>В&nbsp;возрасте 24&nbsp;лет Барни объявился на&nbsp;нью-йоркской сцене с&nbsp;видеоперформансом «Порог в&nbsp;милю высотой: Полёт с&nbsp;анально-садистским воином». Барни в&nbsp;купальной шапочке, в&nbsp;туфлях на&nbsp;высоких каблуках и&nbsp;с&nbsp;титановым ледорубом в&nbsp;анусе лез по&nbsp;потолку галереи, в&nbsp;течении трёх часов сопротивляясь силам тяготения и&nbsp;дискомфорта.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Роберт Гобер «Человек выходит из&nbsp;женщины»</h3>\r\n<h3>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7590255/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7590255-R3L8T8D-600-20120827_R_Gober_untitled_ManComingOut.jpg" /></a></h3>\r\n<h3>\r\n	Гобер лепит из&nbsp;воска различные части тела и&nbsp;в&nbsp;самых неожиданных позах раскладывает их&nbsp;по&nbsp;галерее. Последние, порой декорированные свечами, порой пронзённые пластиковыми дренажными трубками, говорят об&nbsp;уязвимости человеческого тела и&nbsp;о&nbsp;его попранном положении.</h3>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Сара Лукас</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7591305/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7591305-R3L8T8D-600-08-4_lucas_lg.jpg" /></a><a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7592855/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7592855-R3L8T8D-600-5014052269_f85617cf43_z.jpg" /></a></p>\r\n<p>\r\n	Творческие работы Сары Лукас (Sarah Lucas) появлялись в&nbsp;самых значимых журналах и&nbsp;музеях современного британского искусства. По&nbsp;её&nbsp;словам, на&nbsp;фотографиях, коллажах, скульптурах, найденных объектах, инсталляциях и&nbsp;рисунках она рассуждает на&nbsp;темы сексуальных отношений, разрушения и&nbsp;смерти.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Рой Ваара «Человек с&nbsp;третьей ногой»</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7593055/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7593055-R3L8T8D-600-roi_collage.jpg" /></a>Рой Ваара (Rot Vaara)&nbsp;— самый знаменитый современный художник Финляндии, преподаватель университетов США и&nbsp;Европы. Этот человек, критикующий искусство за&nbsp;излишнюю музейность, объявил себя живущим арт-объектом ещё в&nbsp;1982&nbsp;году. С&nbsp;тех пор он&nbsp;и&nbsp;его третья нога сделали 300 уникальных перформансов и&nbsp;приняли участие в&nbsp;200 фестивалях в&nbsp;30&nbsp;странах мира.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Братья Джейк и&nbsp;Динос Чепмен.</h3>\r\n<h3>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7593405/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7593405-R3L8T8D-600-z_cd43377b.jpg" /></a></h3>\r\n<h3>\r\n	Скандальные художники из&nbsp;Британия братья Чепмен вообще прославились благодаря своим многочисленным провокациям, дракам и&nbsp;излюбленной темой половых органов, которые они с&nbsp;одинаковым усердием лепят и&nbsp;на&nbsp;картины старых мастеров, и&nbsp;на&nbsp;носы детям. Эта самая известная их&nbsp;работа представляет из&nbsp;себя&nbsp;удовищно сплавленное воедино кольцо из&nbsp;голых девочек-манекенов в&nbsp;кроссовках с&nbsp;вагинами вместо ртов и&nbsp;ушей или пенисами вместо носа.</h3>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Kaarina Kaikkonen</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7593555/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7593555-R3L8T8D-600-553.jpg" /></a><a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7593505/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7593505-R3L8T8D-600-14.jpg" /></a></p>\r\n<p>\r\n	С&nbsp;первого взгляда может показаться, что добрая половина мужского населения небольшого городка решила высушить одежду. Но&nbsp;нет&nbsp;— это арт-инсталляции финской художницы Kaarina Kaikkonen. Художница тем и&nbsp;прославилась, что научилась аккуратно развешивать разного рода бельишко и&nbsp;размещать все это под разными углами в&nbsp;городской среде. По&nbsp;её&nbsp;словам Kaikkonen, так она хочет понять, где начинается конец всей реальности.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Маурицио Каттелан</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7594005/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7594005-R3L8T8D-600-catt.jpg" /></a></p>\r\n<p>\r\n	Каттелану 50&nbsp;лет, но&nbsp;он&nbsp;до&nbsp;сих пор предпочитает называть своей главной отличительной чертой идиотизм. В&nbsp;прошлом почтальон, уборщик, повар и&nbsp;донор банка спермы, Каттелан, по&nbsp;собственному утверждению, работал для единственной цели&nbsp;— выжить. В&nbsp;опубликованном в&nbsp;британской газете «Гардиан» интервью Каттелан поведал, в&nbsp;частности, о&nbsp;том, что творит «не&nbsp;головой, а&nbsp;желудком», и&nbsp;потому не&nbsp;в&nbsp;состоянии объяснить смысл своих произведений.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Марк Дженкинс</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7594755/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7594755-R3L8T8D-600-1268285167_installations_by_mark_jenkins_50.jpg" /></a></p>\r\n<p>\r\n	Вообще Марк Дженкинс знаменит своими стрит-арт объектами, которые представляют из&nbsp;себя человеческие скульптуры, выполненные из&nbsp;скотча и&nbsp;поэлитилена, которыми он&nbsp;уже давно шокирует жителей Вашингтона и&nbsp;других городов. Также он&nbsp;периодически устраивает вот такие арт-инсталляции. Интересно и&nbsp;то, что художник создал пошаговую инструкцию об&nbsp;изготовлении скульптур из&nbsp;пленки, и&nbsp;проводит мастер-классы в&nbsp;городах, которые он&nbsp;посещает.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Saara Ekström</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7595155/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7595155-R3L8T8D-600-QrENUl2qKdQ.jpg" /></a></p>\r\n<p>\r\n	Ещё одна художница из&nbsp;Финляндии, чье творчество, мягко говоря, неоднозначно. Прославилась Saara Ekström своими инсталляциями, видео, фотографиями и&nbsp;рисунками. В&nbsp;своих работах она использует в&nbsp;качестве материала человеческие волосы, органы и&nbsp;туши животных и&nbsp;тому подобное. На&nbsp;одной из&nbsp;своих выставок финка даже представили татуированные куски кожи свиней. А&nbsp;ещё она преподает в&nbsp;университете.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Такаси Мураками «Мой одинокий ковбой»</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7597055/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7597055-R3L8T8D-600-D5w5KamfjDM.jpg" /></a>Мураками в&nbsp;Японии много, но&nbsp;такой только один. У&nbsp;него есть докторская степень Японского университета искусств, он&nbsp;один из&nbsp;наиболее успешных современных японских художников, а&nbsp;эта авторская скульптура «Мой одинокий ковбой» в&nbsp;2008 была продана на&nbsp;аукционе Sotheby за&nbsp;15&nbsp;миллионов долларов. Помимо этого Мураками рисует мультфильмы и&nbsp;создает детские игрушки.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Аурель Шмидт</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7598555/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7598555-R3L8T8D-500-tumblr_mdnr39nAWS1qz5g75o1_r3_500.jpg" /></a></p>\r\n<p>\r\n	Знакомьтесь, это&nbsp;— Аурель Шмидт из&nbsp;Нью-Йорка. Она, как вы&nbsp;уже догадались, художница, но&nbsp;немногие из&nbsp;вас признают произведениями искусства в&nbsp;её&nbsp;творениях. Юная леди занимается живописью, делает инсталляции из&nbsp;мусора, фотографирует, устраивает перфомансы с&nbsp;собственным участием, которые не&nbsp;обходятся без скандала. Примечательно, что авторитетный журнал Forbes как-то включил её&nbsp;в&nbsp;список самых успешных молодых людей.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Андрес Серрано</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7603505/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7603505-R3L8T8D-500-tumblr_m7cznxe8Sc1ry2rxso1_1280.jpg" /></a></p>\r\n<p>\r\n	Андрес Серрано (Andres Serrano) наполовину фотограф, наполовину гондурасцец, все делал правильно&nbsp;— фотографировал сексуальные сцены, трупы, испражняющихся людей, извращения, но&nbsp;успех к&nbsp;нему никак не&nbsp;приходил. Серрано стал звездой после того, как сделал фотографию «<a href="http://bigpicture.ru/wp-content/uploads/2009/11/1621-677x990.jpg" target="_blank">Piss Christ</a>» («Писающий Христос»). На&nbsp;снимке изображалось распятие, погруженное в&nbsp;мочу фотографа. В&nbsp;художественном плане снимок ничем не&nbsp;выделяется из&nbsp;прочих работ Серрано, но&nbsp;людей зацепило. Из&nbsp;никому не&nbsp;интересного фотографа он&nbsp;стал мировой знаменитостью, получил многочисленные призы, а&nbsp;фотография участвовала в&nbsp;выставках по&nbsp;всему миру. Несколько раз ее&nbsp;пытались уничтожить разгневанные посетители, в&nbsp;связи с&nbsp;этим постоянно росла страховая стоимость снимка и&nbsp;известность фотографа.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Дитер Рот «Гегель, собрание сочинений в&nbsp;20&nbsp;томах»</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7606005/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7606005-R3L8T8D-500-13fCBAaHyOw.jpg" /></a>Кто такой был Дитер Рот? Художник-абсурдист. Искусство Дитера Рота пачкается, воняет и&nbsp;медленно разрушается, и&nbsp;он&nbsp;считал, что так оно и&nbsp;должно быть. Дитер Рот, например, много работал с&nbsp;колбасой. Он&nbsp;измельчил в&nbsp;крошку&nbsp;<nobr>20-томное</nobr>&nbsp;собрание сочинений философа Гегеля, смешал бумагу с&nbsp;салом и&nbsp;сделал колбасу. 20&nbsp;батонов висят в&nbsp;два ряда, внутри колбасы хорошо видны буквы. Поглошение знаний в&nbsp;буквальном смысле слова.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Линда Бенглис</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7606705/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7606705-R3L8T8D-500-z_ddaec8f9.jpg" /></a>От&nbsp;автора: «Большинство моих работ вызывает ощущение физического движения, словно это тело или что-то, побуждающее физиологический отклик...(к примеру), полиуретановые скульптуры наводят на&nbsp;мысль о&nbsp;каких-то волновых образованиях или животных внутренностях, они вызывают чувства, некоторым образом знакомые зрителю, природные чувства...иначе говоря, доисторические. Хотя формы не&nbsp;являются специфически узнаваемыми, чувства&nbsp;— являются». Без комментариев.</p>\r\n', 0, '', '', '', NULL),
	(8, 2, 'article-3', 'Two days in Red Pepper', NULL, '<p>\r\n	Red Pepper - еще одно уникальное порождение загадочной екатеринбуржской атмосферы. Самое раздолбайское агентство страны, которое при этом умудряется успешно работать.</p>\r\n<p>\r\n	Редактор AdMe.ru Ксения Лукичева съездила в гости к "красным перцам" (юридическое название - ООО "Абсолютная власть") с целью разобраться, как же у них это получается.</p>\r\n<p>\r\n	В те два дня, когда я приехала в Red Pepper, в гостях у них была не только я, но и татуировщик, расположившийся в кабинете генерального и креативного директора Данила Голованова и делавший татуировки всем желающим сотрудникам.&nbsp;Кто-то в процессе умудрялся работать, печатая одной рукой письма в то время, когда машинка жужжала над второй рукой, а кто-то давал интервью.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563855-R3L8T8D-600-IMG_1462.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	Данил Голованов решил совместить приятное и болезненное с разговором со мной, для полноты ощущений. Мы беседовали под жужжание машинки, пока мастер выводил на его загривке черные буквы "Made in Ural". В Екатеринбурге все, кого я знаю, отчаянные патриоты Урала, гордятся своим происхождением и носят этот "бренд" кто просто в сердце, а кто теперь и на коже.</p>\r\n<p>\r\n	- Я иногда думаю о том, что под каждое агентство можно подобрать песню или группу, которая будет целиком выражать суть этого агентства.</p>\r\n<p>\r\n	- Да? Например?</p>\r\n<p>\r\n	- Ну вот Восход, например, это Depeche Mode, - не задумываясь, выдает Даня. - Знаешь, такой… Любая песня из сборника "The Best Of".</p>\r\n<p>\r\n	- А StreetArt?</p>\r\n<p>\r\n	- StreetArt - мрачноватые ребята. В себе такие. Поэтому Дельфин, наверное?</p>\r\n<p>\r\n	- А вы? - улыбаюсь я.</p>\r\n<p>\r\n	- Про нас есть отдельный трек. Вот очень про нас. Justise, которая "We! Are! Your friends! You never be alone again, oh come on!", - бодро напевает Даня, шевеля только лицевыми мышцами, потому что иначе татуировка может приобрести ненужное направление.&nbsp;</p>\r\n<p>\r\n	Песню эту я, конечно, помню. Этакая смесь хипстоты с безудержным умением получать удовольствие от жизни. Очень про Ред Пеппер, факт.&nbsp;И вот под этот саундтрек мы и начинаем знакомство с агентством.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	В агентстве сейчас работает 25 человек в екатеринбургском офисе и четверо в Новосибирске. Половина из этих двадцати пяти сидит в кабинете, который проходит под названием "серьезный" - там медийщики, event и все такое прочее очень важное, но, по правде говоря, не особо фактурное. "Ты туда не ходи, скука смертная", говорит Даня, "тебе и нашего веселого офиса хватит".&nbsp;</p>\r\n<p>\r\n	Офис и впрямь не скучен. Например, центральное место в пестром интерьере с преобладающим красным занимает кровать-чердак, на которой можно поспать, и под которой находится "сиротское" рабочее место второго креативного директора агентства Никиты Харисова. Диван, на котором за день засиживаются все, кому не лень, и маленький икеевский придиванный столик.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563255-R3L8T8D-600-IMG_1436.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	Субординация в офисе присутствует только в одном случае - если Голованов уверен в своей правоте. Тогда он включает смесь своей внушительной харизмы и статуса владельца агентства, и коллеги, после жестокого спора, отступают.</p>\r\n<p>\r\n	- Ну да, - смущается Даня. - Иногда у меня бывают навязчивые идеи, и я их проталкиваю, давлю позицией. Бывает, что я оказываюсь прав, а бывает, что и нет.</p>\r\n<p>\r\n	Он кивает головой в сторону Харисова:</p>\r\n<p>\r\n	- Вот этот тип сейчас хочет снять абсолютно говеный ролик!</p>\r\n<p>\r\n	- А ты хочешь снять чертов ералаш! - не остается в долгу Харисов. И понеслась. Я настолько заворожена эмоциями, которыми они искрят и фонтанируют, что напрочь теряю нить и в итоге из-за спектакля пропускаю момент определения победителя.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563805-R3L8T8D-600-IMG_1451.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	Большие дети, занимающиеся рекламой, потому что им прикольно. Собравшие штат из самых разных людей, в основном не имеющих отношений к рекламе. Кладущие болт на субординацию, хохочущие над тем, что у них каждый третий - директор. Два креативных, коммерческий, финансовый, арт-, продакшен-, аккаунт- и медиа-директор. "30 процентов от штата! Больше, чем в Лео Бернетте, наверное", хохочет Харисов, но тут же делает серьезное лицо. "Но это больше влияет не на важность, а на степень ответственности. Этот статус, как ни крути, нужно отработать. Грубо говоря, ты не можешь прийти на работу с бодуна и весь день протупить, валяясь на кровати".</p>\r\n<p>\r\n	Харисов, недавно повышенный до креадира - известный в Екатеринбурге своими эммм… нестандартными вечеринками клубный промоутер и бармен с 10-летним стажем. В рекламу, как сам утверждает, попал случайно. Полтора года назад он участвовал в проекте для одного из ключевых клиентов, снял хороший ролик, и Голованов его пригласил в агентство попробовать заниматься видео. Видео потянуло за собой разработку концептов, копирайтерскую работу, стратегию и так далее. Универсальность и взаимозаменяемость - один из главных принципов агентства.</p>\r\n<p>\r\n	Джуниор-копирайтер Иван Соснин - когда мне его так представляют, я не удерживаюсь от ехидного замечания "Джуниор? Можно подумать, у вас есть другие копирайтеры" -&nbsp; на пятом курсе бросил теплофак, чтобы не дай бог не получить диплом и не работать потом по специальности. Ваня - очень любопытный персонаж. Весь, что называется, не от мира сего - смесь смущенного ботаника и чокнутого художника. Хотела сказать, что он вносит струю сумасшествия в агентство, но потом подумала, что этим занята половина Ред Пеппера во главе с Головановым. А еще Ваня снимает нечеловечски странные клипы, пугающие и завораживающие одновременно, самый понятный из которых -&nbsp;"Завтра" Сансары.&nbsp;</p>\r\n<p>\r\n	У них правда нет стратегов и копирайтеров, а арт-директором еще недавно был лидер группы "Сансара" Александр Гагарин.</p>\r\n<p>\r\n	И при всем при этом у них на годовых контрактах постоянно обслуживаются 3-4 крупных клиента на креативе и медиа, есть стабильный поток из "проектных" клиентов и есть несколько проектов, на которых, по словам Дани, они "ну прям деньги зарабатывают".&nbsp;И это не просто фирстиль и биллборды. Голованов особенно гордится тем, что они умеют работать с полной интеграцией проекта в бизнес. Это, конечно, в результате сказывается на том, что далеко не все можно оформить в красивый кейс, выставить на фестивали и послать на профильные сайты, но креативом можно блеснуть и на других проектах, а подобная работа добавляет +100 к самым разнообразным прикладным навыкам. Про выражение этих плюсов на банковских счетах тоже забывать не стоит.</p>\r\n<p>\r\n	Это все вовсе не значит, что у Ред Пеппера нет фестивальных амбиций - они есть, внушительные и одновременно какие-то дурашливые. Посылают работы и радуются, когда что-то выигрывают, не запариваются, когда пролетают мимо.</p>\r\n<p>\r\n	Об отношении к делу, о работе, о том, кем они хотят быть, когда вырастут, мы говорили с Данилом Головановым под жужжание татуировочной машинки.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563055-R3L8T8D-600-IMG_1431.JPG" /></p>\r\n<p>\r\n	<br />\r\n	- Даня, расскажи мне вот что. Я знаю тебя уже третий год, но не в курсе, откуда у тебя все это - свое агентство, городской сайт, два бара. Тебе же всего 26.</p>\r\n<p>\r\n	- В шутку все началось. Мы делали вечеринки в клубах, промоутерами были. В какой-то момент Даня Ерохин&nbsp;(партнер Голованова - прим. ред.)&nbsp;пришел и предложил открыть агентство, которое будет обслуживать пару магазинов одежды и один банк в плане эвентов. Ну мы и открыли&nbsp;(ухмыляется). Поработали года полтора - набирались опыта, делали какие-то абсолютно дурацкие ошибки, очень смешные. Случился кризис, и один из наших тогдашних клиентов, банк, попросил нас поразмещать ему наружку. Никто, кроме нас, не готов был делать это без предоплаты. Мы сказали клиенту, что не умеем, а он такой "Научитесь, там ничего сложного".</p>\r\n<p>\r\n	И сначала я лично размещал эту наружку первые два месяца, а потом пригласили человека, и так у нас появился медиа-отдел в агентстве из 3-4 человек. Через какое-то время мы стали заниматься креативом, и вот…</p>\r\n<p>\r\n	- Получается?</p>\r\n<p>\r\n	- Мы все хотим, конечно, пойти в сторону Восхода, Инстинкта - делать офигенные кампании задорого и специализироваться на креативе. Но это у нас пока получается тяжело - мало кто в состоянии дать агентству глобальные ролики, дорогие проекты с хорошим продакшеном. Поэтому нам приходится находить себе работу, которая больше интегрирована в бизнес клиентов. Наш проектный отдел делает все под ключ, это же огромный объем работ - мы заходим в проект на уровне финансового планирования, бизнес-плана, рентабельности, и очень часто бывает, что заработаем мы денег или нет зависит от того, насколько хорошо мы сделаем этот проект.</p>\r\n<p>\r\n	Еще делаем очень много такой работы, которую никому нельзя показывать, как и многим агентствам, которые с табачными компаниями работают. Но это вообще отдельная история.</p>\r\n<p>\r\n	- Этим у вас как раз тот кабинет серьезный занимается?</p>\r\n<p>\r\n	- Нет, тот кабинет занимается медийкой только и мероприятиями. Проектные менеджеры и аккаунты здесь сидят. У нас как вообще получается: все проекты поступают в креативный отдел, мы придумываем общее направление, пишем бриф для проектников, потом они уже по нему начинают работать. Причем это может быть абсолютно разного уровня креатив, от сценария видеоролика до разработки мобильных приложений, мы даже сделали социальную сеть для одного бренда. Еще с нами все подрядчики очень любят по мероприятиям работать.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563455-R3L8T8D-600-IMG_1438.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	- Как так получилось, что у вас сплошная молодежь, тебе 26 лет, вы вообще такое раздолбайское агентство, а ведете такие крупные проекты?</p>\r\n<p>\r\n	- Агентство раздолбайское до поры до времени. Мы же еще и деньги зарабатываем, зарплаты у нас… выше рыночных, я бы сказал. И у нас у всех есть очень четкое ощущение наступления пи*деца. И есть понимание, что любой проект зависит от каждого человека в агентстве, есть чувство ответственности. Поэтому мы собираемся в какой-то момент и выжимаем себя.</p>\r\n<p>\r\n	- Под дедлайн?</p>\r\n<p>\r\n	- Да, под дедлайн. У нас же нет специалистов. Первый специалист, которого мы пригласили, это Карюкин&nbsp;(Андрей Карюкин до Red Pepper проработал несколько лет сейлзом в "Восходе" - прим.ред.). До этого не было. И пока креативщикам и медийщикам тяжело с ним работать, у него свои стандарты, бюрократия… а у нас все так более свободно, в кайф.</p>\r\n<p>\r\n	- Представь себе, что твои сотрудники уходят в другое агентство. Какое это агентство могло бы быть из российских? Где бы они прижились лучше всего?</p>\r\n<p>\r\n	- Мне почему-то кажется, что Ред Кедс, Твига - по духу. Но по факту - скорей Leo Burnett.</p>\r\n<p>\r\n	- По какому такому факту?</p>\r\n<p>\r\n	- У нас есть опыт - и достаточно большой - в работе с определенным клиентом&nbsp;(очевидно, имеется в виду Philip Morris - прим. ред.). Все наши знают нормы, требования, документацию этого клиента, знают все пути рекламы для него, это уже готовые сотрудники под конкретных клиентов.</p>\r\n<p>\r\n	И это точно не Восход - мне Губайдуллин как-то говорил, что он никого бы из наших не взял&nbsp;(смеется)</p>\r\n<p>\r\n	- А кого бы из нынешних российских рекламщиков ты хотел бы видеть в своем агентстве? Даже если это мечты и совсем невозможно.</p>\r\n<p>\r\n	-&nbsp;(загибает пальцы)&nbsp;Ну… Примаченко, пол-Инстинкта, пол-Восхода, весь диджитал Ред Кедс, Кинограф бы взял в качестве продакшена&nbsp;(смеется). Грейт еще нравится в последнее время. Твига нравится. Но больше всего нравится Инстинкт. Они делают примерно то же самое, что и Восход, только для огромных брендов, а это же очень сложно, практически невозможно.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563555-R3L8T8D-600-IMG_1441.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	- Какие у вас цены по Екатеринбургу? Не самые высокие?</p>\r\n<p>\r\n	- Не. Ну смотря как оценивать. В плане организации эвентов и каких-то нестандартных механик, у нас точно самый высокий прайс. Мы не беремся за проекты, которые не соответствуют нашим внутренним понятиям о том, как оно должно быть, фигней не занимаемся.&nbsp;А по креативу у Восхода, конечно, самый большой.&nbsp; Мы, наверное, даже в тройку не входим.</p>\r\n<p>\r\n	- Где вы учитесь?</p>\r\n<p>\r\n	- Нас клиенты учат.</p>\r\n<p>\r\n	И мы хотим видепродакшен развивать - ролики-ролики-ролики-ролики-ролики-ролики… Очень много идей, снимаем кучу роликов, которые в итоге не показываем никому, потому что качество плохое. Здесь в Екатеринбурге никто, кроме Губайдуллина, как мне кажется, снимать не умеет. А Губайдуллина на всех не хватит - ему некогда, у него своя работа.</p>\r\n<p>\r\n	Если разобраться - вроде бы ничего сложного, но сами так не можем пока.</p>\r\n<p>\r\n	Еще хотим развивать айдентику. Набрали очень много талантливых дизайнеров. Вот Ирина, арт-директор наш, она талантливая очень, безумно работоспособная, и при этом это человек, который спокойно с первого раза делает макеты для федеральных кампаний международных клиентов. И все всегда очень довольны ее работой. Она знает, что нужно клиенту.</p>\r\n<p>\r\n	Я думаю, что в этом году у нас будет упор именно на рекламную рекламу. Не на маркетинг, как было раньше. Это очень легко оказалось - в рейтинге АКАРа в списке маркетинговых сервисов мы на втором месте. Представляешь?</p>\r\n<p>\r\n	- Ну потому что этим обычно очень тихо занимаются.</p>\r\n<p>\r\n	- Ну да&nbsp;(смеется). Обычно это еще и неинтересно бывает. А у нас идей много. Главное - придумать оригинальную коммуникацию с потребителем. У нас был директ для табачников, когда мы выбирали бренд-амбассадора. Привезли в город две феррари, девочки знакомились с парнями в соцсетях, договаривались встретиться где-то в центре города. Они подъезжали на феррари, парни садились в машину, им надевали на головы пакеты, увозили в театру драмы, заводили с черного входа, снимали пакеты, и они оказывались в очень ярком освещении, где сидело очень много людей, и у них брали интервью, способен ли он работать бренд-амбассадором или нет. Заставляли их петь, танцевать стриптиз, что угодно. Знаешь, если бы это был не табачный клиент, можно было бы такой кейс сделать сумасшедший… У некоторых людей истерика прям реально была, некоторые удивляли идеями еще больше, чем мы сами. Интересно было.</p>\r\n<p>\r\n	Я люблю такое.&nbsp;Как Duval Guillaume делает. Кто бы что ни говорил про то, что это фейк, но когда видео набирает такое количество просмотров и становится вирусом, когда оно попадают в топ-20 видео Ютуба, я считаю, что это прямо такая документальная реклама получается. Потому что по сути они снимают просто документальные короткометражки про розыгрыши, но при этом находят офигенный инсайт для бренда и очень грамотно все монтируют. В этом году мы что-нибудь такое обязательно сделаем прикольное. Есть мысли...</p>\r\n<p>\r\n	А еще мне&nbsp;Вайден&nbsp;нравится.</p>\r\n<p>\r\n	- А кому не нравится Вайден?</p>\r\n<p>\r\n	- Очень много кому.</p>\r\n<p>\r\n	- Да ладно?</p>\r\n<p>\r\n	- Ну, допустим, многие считают Вайден агентством, которое очень сильно переигрывает. Увлекается. Что они отходят от сути. Я тебе сейчас объясню, что я имею в виду. Они делают рекламу будущего, которая зачастую кажется непонятным не только потребителям, но и даже некоторым моим коллегам. У нас был очень долгий разговор с кем-то из наших рекламщиков про&nbsp;Heineken "Entrance", они говорили, что они понимают, насколько это все красиво и круто, но не понимают, что именно этим роликом хотел сказать сам бренд. Я говорил им, что это искусство, кино… Может завидуют просто, не знаю.</p>\r\n<p>\r\n	- Как им можно завидовать, если они лучше всех? Зависть объяснима, когда она к соседу, который более успешен, чем ты, и лучше живет. А это же верхняя планка.</p>\r\n<p>\r\n	- Хорошо, что в то время, когда Восход поднимался, мы еще всем этим не так увлекались. Мы бы тоже завидовали&nbsp;(смеется)&nbsp;А так получилось, что они помогли нам вырасти. Они помогли, помогла Идея, когда мы второй раз туда приехали. Уверенность в своих силах почувствовали. Щас мы хотим, конечно, в плане фестивалей международную награду получить, потому что у нас их пока почти нет.</p>\r\n<p>\r\n	- А что ты хочешь?</p>\r\n<p>\r\n	- Я хочу Канны, конечно. Евробест, какой-нибудь крутой новый фестиваль типа PIAF. У нас есть там шорты, но я хочу золото.</p>\r\n<p>\r\n	Хочется просто сделать рекламу, которую будут смотреть десятки миллионов людей. Хочется сделать по-настоящему крутую социалку, которая будет не просто красивая и трендовая, а которая будет работать, как Восход. По сути это первая социалка, которая сработала в России, потому что до этого…&nbsp;(морщится, задумывается)</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563405-R3L8T8D-600-IMG_1449.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	Знаешь, мы в рекламу играем. Для нас это не работа, это образ жизни такой. Наверное, это и есть один из трендов современного рынка рекламного, что можно вот так вот взять, пойти и начать делать что-то новое, чего раньше никто не делал. То, что сейчас происходит в российском профессиональном сообществе, это… Это очень узко, такая старая тусовка со своими правилами, законами, которым не нравится, что все вокруг меняется. Поэтому Восход вырос, потому что они готовы были к переменам. Поэтому мы появились, потому что мы не заморачиваемся на важности.</p>\r\n<p>\r\n	Если есть азарт, какая-то идейная молодость у агентства, у конкретных людей, то это очень много значит. И главное - нельзя стесняться того, что делает агентство. У меня есть много знакомых из сетевых агентств, которые свои работы вообще не показывают.</p>\r\n<p>\r\n	- Стыдно им.</p>\r\n<p>\r\n	- У них есть комплекс названия, аббревиатуры, которая висит над ними, а у нас нет, нам в этом плане проще. Мы, конечно, тоже не показываем всего, что мы делаем, потому что иногда делаем совсем говно. Но это все равно шаг вперед хоть какой-то. Я даже лекцию как-то читал, где показывал только говно, которые мы сделали. Кейсы нормальные и так везде можно в сети посмотреть, а говна не найдешь, все прячут, стесняются. Лекция на ура, люди прямо ржали над нами.</p>\r\n<p>\r\n	- У вас юрлицо называется ООО "Абсолютная власть", это круто.</p>\r\n<p>\r\n	- Да. Это все само рождается, эти шутки все. Просто надо легче ко всему относиться.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563955-R3L8T8D-600-IMG_1473.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	Никита Харисов, уже описанный ранее второй креативный директор с сиротским рабочим местом и аж двумя новыми, саднящими татуировками, сделанными в кабинете у Голованова без отрыва от производства, еще сам не до конца понял, как его занесло в рекламу и что он тут делает. Днем - рекламист, ночью - клубный промоутер, известный трэш-вечеринками. Описание такое, что не сразу заподозришь, что человек в действительности уже во все врубился и куда успешней многих, кто настойчиво идет в рекламу годами.</p>\r\n<p>\r\n	Харисов воспринимает рекламу не настолько серьезно, она не стоит у него на золотом пьедестале, а в красном углу нет иконостаса. Это работа и веселье, это то, что позволяет ему фантазировать и решать интересные задачи. Еще один необычный персонаж екатеринбургской рекламы.</p>\r\n<p>\r\n	<br />\r\n	- У меня нет никаких амбиций по завоеванию мирового рынка или там фестивальных наград. Я просто люблю придумывать, фантазировать. А эта работа дает еще возможность все это структурировать и доносить до публики правильным месседжем.</p>\r\n<p>\r\n	- Что доносить? Твои фантазии?</p>\r\n<p>\r\n	- Мои фантазии, то, что я хочу сказать. Это учит тебя разговаривать с аудиторией. Приходит клиент, и тебе интересно, как он будет говорить со своей аудиторией. Грубо говоря, ты являешься языком и ртом клиента. От тебя очень много зависит, и это такая… власть…</p>\r\n<p>\r\n	- ООО "Абсолютная власть", ага.</p>\r\n<p>\r\n	-&nbsp;(хохочет)&nbsp;Да. Наверное, поэтому мы так и называемся.</p>\r\n<p>\r\n	- Твои фантазии же, получается, ограничены очень сильно тем, что хочет клиент, тем, что он не принимает что-то, выкидывает самое интересное на твой взгляд.</p>\r\n<p>\r\n	- Чем больше ограничений, тем интересней работать. Когда у тебя полный полет, ты начинаешь просто придумывать трэш. Ведешь себя как художники-импрессионисты или представители современного искусства. Они просто делают непонятные штуки. А тут ты начинаешь задумываться об эффективности. Есть четкая задача, и ты рисуешь какую-то картину, которая должна передать определенную мысль тому, кто на нее смотрит.</p>\r\n<p>\r\n	- А на вечеринках своих ты уже как представитель современного искусства выступаешь?</p>\r\n<p>\r\n	- Да&nbsp;(смеется). На своих вечеринках я уже отрываюсь по полной. Восстанавливаю баланс.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563755-R3L8T8D-600-IMG_1456.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	- Многие в российской рекламе относятся к клиентам, как к мудакам. Как относятся к клиентам в Red Pepper?</p>\r\n<p>\r\n	- Я считаю точку зрения, что клиенты - мудаки,&nbsp;(задумывается)&nbsp;несколько необоснованной. Клиент - это тот, кто дает нам работу. мы не свободные художники, мы созданы для того, чтобы работать на кого-то. Конечно, бывают клиенты, которые чего-то не понимают. Или хотят слишком многого, но при этом ставят миллиард ограничений. Есть люди, которые видят картинку, которую они хотят, но при этом почему-то не хотят в этом признаваться, и только когда ты уже даешь ему продукт, он говорит: "нет, я хочу вот так, так и так". Ты спрашиваешь у него, как это должно выглядеть, а он: "Я не знаю! (хлопает глазами)&nbsp;Вы же рекламное агентство, вы же креативщики, вот и думайте".</p>\r\n<p>\r\n	Клиенты бывают разные, но с этим нужно просто смириться, нужно уметь убеждать, уметь аргументировать свою точку зрения. Хотя, конечно, не всегда это и получается.</p>\r\n<p>\r\n	- Ругаетесь с клиентами?</p>\r\n<p>\r\n	- Нет. Почти нет. Больше всего мы спорим между собой с Данилом. Хотя и спорить с ним все равно бесполезно&nbsp;(корчит мину), я пытаюсь до конца отстаивать свою точку зрения.</p>\r\n<p>\r\n	- А почему с ним бесполезно спорить?</p>\r\n<p>\r\n	- У Данила есть один мощный плюс: он умеет правильно и быстро подобрать аргументацию. У меня в этом пока такой небольшой затык, я не очень умею оперативно мыслить: мне нужно сесть одному, подумать в тишине минут 5-10, и тогда у меня что-то рождается.<br />\r\n	Поэтому у нас на встречах с клиентами я почти всегда молчу и слушаю, что-то себе на ус наматываю, а потом могу даже просто выйти из кабинета, и у меня уже родится мысль. Я считаю, что лучше все сначала обдумать, чем ляпать сразу.</p>\r\n<p>\r\n	- Используете какие-то традиционные методы придумывания идей типа мозговых штурмов? Как вообще у вас это происходит?</p>\r\n<p>\r\n	- Да по-разному. В основном у нас идеи три человека придумывают: я, Данил и Ваня Соснин. Обычно либо мы с Ваней сидим и обсуждаем все в диалоге, и у нас рождаются идеи, и потом уже одну дорабатывает Ваня, одну я. Либо каждый из нас вынашивает по одной идее, потом мы вместе собираемся, обсуждаем, находим плюсы и минусы, стараемся вместе доработать и превратить в конечный продукт.</p>\r\n<p>\r\n	- А Голованов как участвует?</p>\r\n<p>\r\n	- Бывает, что он звонит в два часа ночи с воплем "Аааа! Я придумал!!". Но обычно он тоже придумывает одну идею - мы же по три идеи клиенту сдаем. Критикуем идеи друг друга, говорим "О, вот это прикольно, вот здесь нужно доработать". То есть Ваня очень хорошо придумывает оболочку - внешние какие-то вещи, копирайты. Но у него бывает сложно с инсайтами. Слоган прикольный, визуал прикольный, но непонятно, что он хочет этим донести. А у меня наоборот. Мне проще находить инсайты и месседжи, но как все это обернуть - бывают проблемы. Сознание путанное у меня (смеется), поэтому работаем в тандеме.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563705-R3L8T8D-600-IMG_1460.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	- А почему тебе на фестивали пофиг?</p>\r\n<p>\r\n	- Я считаю, что лучшая награда - это, во-первых, эффективность рекламной кампании, а во-вторых, если простые люди заметят ее и будут обсуждать. Большинство работ на фестивалях, как мне кажется, это реклама ради рекламы, современное искусство. Мы что-то хотим сказать, но поймут это только рекламщики - идеи, инсайты, экзекьюшен. А простые люди, даже образованные, они же этого не видят, не знают всего этого.</p>\r\n<p>\r\n	- Какой работой своей ты гордишься? Чего ты прикольного придумал?</p>\r\n<p>\r\n	- Наверное, это больше не креатив, а разработка. Я вел этот проект от начала и до конца - приложение для одного табачного бренда. Я полностью продумал структуру, механику, дизайн мы вместе с Ириной делали. Наверное, вот это. Я считаю, что приложение имеет будущее, и с учетом того, например, что рекламы в табачной промышленности становится все меньше и меньше, и скоро все уйдет в мобильные приложения. Интернет-сайты - это, конечно, хорошо, но они не прикладные в основном, а мобильные приложения - прикладные и всегда с собой.</p>\r\n<p>\r\n	- Интересно. Обычно гордятся каким-нибудь креативом.</p>\r\n<p>\r\n	- Наверное, я просто еще не создал такой креатив, которым мог бы гордиться.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563505-R3L8T8D-600-IMG_1437.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	В Red Pepper всегда слышно Голованова, Харисова, Карюкина и проект-менеджера Артема Зверева. Они самые разговорчивые, вечно смеющиеся и громогласные. В эту канву вплетаются голоса других сотрудников, и только из одного угла офиса практически никогда никого не слышно - четыре компьютера, четыре человека сосредоточенно смотрят в монитор, щелкают клавиатурами и мышками. Это дизайнеры под предводительством арт-директора Ирины Коротич, и если абстрагироваться от всего и какое-то время понаблюдать за ними, то можно запросто впасть в медитативное состояние, а потом решить, что вот, похоже, только дизайнеры-то тут и работают.</p>\r\n<p>\r\n	Моя попытка отвлечь Иру на разговор в диктофон ни к чему не привела - Ира смеялась, сияла ямочками на щеках, односложно отвечала и все время посматривала на монитор. Очевидно, она нужна агентству как воздух - просто для баланса. Наш разговор прервал Голованов: "Ира - трудоголик, она не умеет отдыхать!" - "А ты - плохой начальник", парировала Ира. "Мне к вечеру вот макет нужен" - "Сделаем", кивает самый тихий и спокойный арт-директор в мире и уходит в работу.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563605-R3L8T8D-600-IMG_1444.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	Вечером второго дня мы мигрируем из одного бара в другой, отмечая день рождения Андрея Карюкина, чья должность на визитках указана, как sales manager, но по сути он выполняет функции коммерческого директора ("у нас тут все директора", помните?).</p>\r\n<p>\r\n	Именинник, разговорившись, подвел прекрасный итог поездке и попыткам понять, как у Red Pepper получается вести успешный бизнес.</p>\r\n<p>\r\n	- Red Pepper - это just for fun. Большая такая семья, кореша, которые вместе делают бизнес. Агентство сейчас находится на стадии роста - приходят большие клиенты, большие бюджеты. Сейчас - тот переломный момент, после которого Red Pepper или подрастет и рестуктуризируется, или уйдет с рынка.</p>\r\n<p>\r\n	- Атмосферу сохранить не получится?</p>\r\n<p>\r\n	- Может и получится. Все от Дани зависит. Понимаешь, в бизнесе есть определенные правила игры, доказательная база, что вот так все и должно существовать и действовать, а он существованием Red Pepper всю эту базу на корню опровергает. Вот он утром приходит в офис, ему легко, весело и интересно. Он никого не дрючит, никого не песочит, ни на кого не орет, никаких протокольных вещей и процессов прописанных нет, но все работает. Как? Х*й знает.</p>\r\n', 0, '', '', '', NULL),
	(9, 2, 'article-4', 'Crowdsourcing in online advertising', NULL, '<div>\r\n	Crowdsourcing - the solution of problems by the many volunteers. In advertising, the organization is characterized by large numbers of people («crowd» - the crowd) to implement the requirements of the brand. The crowd is in a state of delight from the opportunity "to bring a piece of themselves" in general a big deal.</div>\r\n<div>\r\n	One of the first crowdsourcing mastered the Communists. Since 1919 the crowd of proletarians in unison out on the so-called voluntary work.</div>\r\n<div>\r\n	The emotional charge of the big work has tremendous power.</div>\r\n<div>\r\n	Naturally, the clever wise advertisers sharpened tools and adapted for their own purposes.</div>\r\n<div>\r\n	Just imagine, you come in Starbucks, the coffee and then cooked personally by your recipe! Moreover, this divine drink every day millions of people drink! The heart rejoices!</div>\r\n<div>\r\n	Starbucks is on the "edge of the attack» crowdsourcing in big business. The lion\'s share of publications in the main community brand in Facebook (&gt; 34 million. Likes), make posts about the success of the project My Starbucks Idea. My Starbucks Idea - is a site aggregator ideas of customers by category: location, technology, recipes, cards, etc.</div>\r\n<div>\r\n	The project is now exactly 5 years and 1 month, posted on the website users 156 482 ideas (84 points / day). Implemented 277. Not bad, considering the fact that the website exists only in English.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	SAS</div>\r\n<div>\r\n	A similar, but slightly less ambitious project launched airline SAS. In the spring of 2012 launched My SAS Idea. The site where travelers can share their ideas on how to improve airline: where to open the flight, which will design mugs, etc.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	A couple of examples:</div>\r\n<div>\r\n	- With the help of the project website airline identified a new direction. One week were asked to 180 cities, top 10 put up for a vote to Facebook. The winning Antalya.</div>\r\n<div>\r\n	- The next time you are at stake was the design used on board cups. For a week the company received 750 options.</div>\r\n<div>\r\n	«My SAS Idea» ... do you not like? Perhaps the three paragraphs above, was the project «My Starbucks Idea»? Further more. Slogans in the studio:</div>\r\n<div>\r\n	- 2012: My SAS Idea. Share. Vote. Comment.</div>\r\n<div>\r\n	- 2008: My Starbucks Idea. Share. Vote. Discuss. See.</div>\r\n<div>\r\n	It becomes clear whose example "inspired» SAS. Well, great minds think alike.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Starbucks and SAS big fellows. These companies have managed to transform its audience into a never-ending source of current solutions. Creative key hits on a regular basis.</div>\r\n<div>\r\n	There are several very similar to My blablabla Idea, projects. This: Dell and the site of its Idea Storm, a Norwegian financial group DNB DNB Labs with the project, and others.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	MacDonald\'s</div>\r\n<div>\r\n	Of course, not everyone is ready to walk so far. There are excellent examples of "topical" toolkit crowdsourcing. One such campaign «Mein Burger» («My Burger"), organized by the MacDonald\'s in Germany on the occasion of the 40th anniversary.</div>\r\n<div>\r\n	MacDonald\'s opened a website where fans of fast food can build your own burger. Then advertise your creation in the social. networks and even offline.</div>\r\n<div>\r\n	German burghers burgerostroeniem so enthusiastic that the advertising campaign Mein Burger became the most successful in the history of MacDonald\'s!</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Here are the results:</div>\r\n<div>\r\n	- 7 million visitors to the page.</div>\r\n<div>\r\n	- 116 000 created burgers for 5 weeks. New burger born every 26 seconds.</div>\r\n<div>\r\n	- 12,000 created advertising campaigns.</div>\r\n<div>\r\n	- 1.5 million people took part in the vote.</div>\r\n<div>\r\n	- 17 million - the total coverage of the campaign on the Internet. Every fourth Internet user in Germany!</div>\r\n<div>\r\n	I won burger «Pretzelnator», comprising: a ham, American and Italian cheese and bun-pretzels.</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Formula</div>\r\n<div>\r\n	I suspect that there is no single recipe for a successful crowdsourcing campaign. As we have seen above, "Saturday" can be used as the basis of the innovation process in the company (Starbucks) and as "local drug exposure» (MacDonald\'s).</div>\r\n<div>\r\n	There are some simple recommendations based on common sense. They will help you to organize an effective crowdsourcing (podsmotreno here: 5 Tips for Crowdsourcing Your Next Marketing Campaign):</div>\r\n<div>\r\n	1) Clearly specify the task. For example: to make the best burger.</div>\r\n<div>\r\n	2) Have a good incentive: the prize, fame, etc. The more we love your brand among customers, the less significant incentive. Starbucks is not particularly bother J</div>\r\n<div>\r\n	3) Do not overload the participants.</div>\r\n<div>\r\n	4) Be prepared for the shaft of good ideas and slag. So you need to consider a system of separating the grain from the chaff.</div>\r\n<div>\r\n	5) Crowdsourcing does not mean "unprofessional." Among your clients may well be excellent designers, copywriters, cooks and engineers.</div>\r\n<div>\r\n	Use of crowdsourcing extremely positive impact on the image of the company. Use your performance and crowdsourcing involvement (engagement) go through the roof, and hair brand managers are soft and silky J</div>\r\n<div>\r\n	But seriously, crowdsourcing, and the truth is a very useful strategy. You will be able to accumulate a huge number of ideas, opinions, become much closer to the customer. Even if you are a giant like Starbucks, SAS, Dell or MacDonald\'s.</div>\r\n<div>\r\n	Naturally, the words are a bit easier than in reality. The described projects are aerobatics, for the execution of which will require great skill and management will ...</div>\r\n<div>\r\n	If you know of interesting examples of crowdsourcing campaigns, especially in Russia, welcome to comment!</div>\r\n<div>\r\n	 </div>\r\n<div>\r\n	Author: Sergey Kokarev, creator agency Primax</div>\r\n', 0, '', '', '', 1365710400);
/*!40000 ALTER TABLE `article_en` ENABLE KEYS */;

-- Дамп структуры для таблица cms.article_ru
DROP TABLE IF EXISTS `article_ru`;
CREATE TABLE IF NOT EXISTS `article_ru` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `alias` char(60) NOT NULL,
  `title` char(150) NOT NULL,
  `text1` mediumtext,
  `text2` longtext NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `metaTitle` varchar(255) DEFAULT NULL,
  `metaKeyword` varchar(255) DEFAULT NULL,
  `metaDescription` varchar(255) DEFAULT NULL,
  `date` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoryId` (`categoryId`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.article_ru: 9 rows
/*!40000 ALTER TABLE `article_ru` DISABLE KEYS */;
INSERT INTO `article_ru` (`id`, `categoryId`, `alias`, `title`, `text1`, `text2`, `sort`, `metaTitle`, `metaKeyword`, `metaDescription`, `date`) VALUES
	(1, 0, 'index', 'Статья на главной странице', NULL, '<p>Главная страница&nbsp;– это информация, которая предстаёт перед пользователем при переходе его по адресу сайта. Другими словами, главная страница – это первое, с чем сталкивается посетитель, оказываясь на сайте. Правилу этому подчиняются все сайты в Интернете – контент-провайдеры, модные интернет-магазины, мощные порталы и многолюдные форумы. Предназначение главной страницы любого сайта – это обеспечение такого «приёма» посетителя, чтобы, в идеале, он стал пользователем. Или, по крайней мере, чтобы задержался на сайте в течение длительного времени.</p>', 0, 'meta Главная страница', 'meta Ключевые слова', 'meta Описание', NULL),
	(2, 0, 'about', 'О нас', NULL, '<p>\r\n	Раздел «О компании» чрезвычайно важен для корпоративного сайта или интернет-магазина и при правильном использовании может поднять продажи. Люди все больше интересуются товарами и услугами не только с точки зрения их полезности. Они хотят приобретать товары и услуги у компаний, имеющих историю и значение. Они хотят знать больше о том, у кого они покупают, и бремя информирования их об этом чаще всего ложится всего на одну страницу. Что касается интернет-магазинов, то они часто не придают значения страницам «О нас», в то время как их роль в онлайн-покупках стремительно растет.</p>\r\n', 0, '', '', '', NULL),
	(3, 1, '130412', 'Михаил Бабич', '<p>\r\n	13 апреля 2013 года в Нижнем Новгороде министр здравоохранения РФ Вероника Скворцова и полномочный представитель Президента России в ПФО Михаил Бабич проведут совещание по реализации мероприятий региональных программ модернизации здравоохранения субъектов ПФО. Об этом сообщает пресс-служба полномочного представителя президента РФ в ПФО</p>\r\n', '<p>\r\n	 </p>\r\n<p>\r\n	13 апреля 2013 года в Нижнем Новгороде министр здравоохранения РФ Вероника Скворцова и полномочный представитель Президента России в ПФО Михаил Бабич проведут совещание по реализации мероприятий региональных программ модернизации здравоохранения субъектов ПФО. Об этом сообщает пресс-служба полномочного представителя президента РФ в ПФО,</p>\r\n<p>\r\n	Выездное совещание с участием главы Минздрава РФ организовано по инициативе приволжского полпреда, такое мероприятие в стране проходит впервые.</p>\r\n<p>\r\n	Руководители органов исполнительной власти в сфере здравоохранения из всех субъектов округа будут защищать программы развития здравоохранения своих регионов до 2020 года. Целевые показатели этой работы заложены в майском Указе Президента РФ №598 и предусматривают: повышение эффективности оказания медицинской помощи, увеличение продолжительности жизни россиян, снижение заболеваемости и смертности населения от наиболее значимых заболеваний путем обеспечения доступности качественной медицинской помощи каждому гражданину страны, а также улучшение состояния региональной инфраструктуры учреждений здравоохранения.</p>\r\n<p>\r\n	Уровень проработки региональных программ развития здравоохранения ПФО лично оценят министр здравоохранения РФ и приволжский полпред. Подобный формат совещания позволит регионам ПФО максимально тщательно проработать свои программы, которые должны быть окончательно утверждены до 1 мая 2013 г., а округу в целом подойти к реализации указа Президента РФ более системно.</p>\r\n', 0, '', '', '', 0),
	(4, 1, '130411', 'Я лично всегда готов принять любого нижегородца, который приходит с дельным предложением - В.Хохлов', '<p>\r\n	Мы всегда готовы к диалогу с представителями общественных организаций, равно как и меценатами или частными компаниями, заинтересованными в сохранении историко-архитектурного наследия. Главное, чтобы этот диалог был конструктивным, направленным на решение проблемы.</p>\r\n', '<p>\r\n	"Мы всегда готовы к диалогу с представителями общественных организаций, равно как и меценатами или частными компаниями, заинтересованными в сохранении историко-архитектурного наследия. Главное, чтобы этот диалог был конструктивным, направленным на решение проблемы. При управлении государственной охраны объектов культурного наследия действует научно-экспертный совет, где собираются наиболее авторитетные эксперты, чтобы дать свои рекомендации. Я лично всегда готов принять любого нижегородца, который приходит с дельным предложением", - заявил журналистам Владимир Хохлов, комментируя итоги работы по сохранению историко-архитектурного наследия Нижегородской области. "Как показывает практика, привлечение частных инвесторов к реставрации памятников архитектуры оправдывает себя. Многие памятники были удачно приспособлены под современное использование, принося доход владельцам, - и при этом оставаясь украшением города.</p>\r\n<p>\r\n	Конечно, действия собственников объектов культурного наследия необходимо строго контролировать. Случаи, когда собственник памятника самовольно его разрушал, в Нижегородской области единичны, но, к моему великому сожалению, такое бывало. Главное: мы не позволяем сделать на месте разрушенного памятника какой-то новый объект – наоборот, понуждаем собственника к восстановлению старинного здания по сохранившимся документам. Это наша принципиальная позиция", - сказал В.Хохлов.</p>\r\n<p>\r\n	"Например, здание № 24 по ул. Новой в Нижнем Новгороде. По данному дому был разработан проект реставрации и приспособления для современного использования. Однако, памятник застройщиком был полностью разобран. По инициативе управления и при содействии прокуратуры Нижнего Новгорода, застройщика обязали воссоздать дом № 24 по ул. Новой. Восстановление здания в настоящее время успешно завершено", - подчеркнул В.Хохлов.</p>\r\n<p>\r\n	Напомним, в конце 2012 года губернатор Валерий Шанцев в своем блоге в «Живом Журнале» сообщил, что, по решению нижегородских блогеров и экспертов, 2013 год в регионе объявлен Годом национального культурного наследия. Валерий Шанцев подчеркнул, что история подарила Нижегородской области очень богатое наследие, и за последние годы сделано немало для его сохранения. «Восстановлено около 300 памятников, в том числе, Зачатская башня, дом Рукавишниковых, дом Бугровых, Сироткина, Шуховская башня… Это наследие, доставшееся от наших предков, которое мы должны ценить и беречь», - написал губернатор. Глава региона напомнил, что в 2012 году область отметила 400-летие народного ополчения – значимый исторический праздник, напоминающий о роли каждого поколения в истории родной страны.</p>\r\n', 0, '', '', '', 1365624000),
	(5, 1, '13-410', 'Кладбище «Новое Стригинское» будет закрыто для новых захоронений позже', '<p>\r\n	Согласно постановлению, которое подписал глава администрации Нижнего Новгорода Олег Кондрашов, внесены изменения в сроки закрытия муниципального кладбища «Новое Стригинское». Об этом сообщает управление по работе со СМИ администрации Нижнего Новгорода. Введение запрета на новые захоронения на погосте перенесено на 15 октября 2013 года.</p>\r\n', '<p>\r\n	 </p>\r\n<p>\r\n	Согласно постановлению, которое подписал глава администрации Нижнего Новгорода Олег Кондрашов, внесены изменения в сроки закрытия муниципального кладбища «Новое Стригинское». Об этом сообщает управление по работе со СМИ администрации Нижнего Новгорода. Введение запрета на новые захоронения на погосте перенесено на 15 октября 2013 года.</p>\r\n<p>\r\n	Напомним, постановление администрации Нижнего Новгорода «О закрытии муниципального кладбища «Новое Стригинское» было принято 13 марта 2012 года. Ранее сроки введения запрета на осуществление новых захоронений уже переносились на 15 мая и 15 ноября 2012 года, а также на 15 апреля 2013 года.</p>\r\n<p>\r\n	Сроки изменены в связи с наличием свободных мест для осуществления новых захоронений на существующей территории кладбища.<br />\r\n	«Мы понимаем, что прежде, чем закрывать то или иное кладбище для новых захоронений, необходимо предоставлять нижегородцам альтернативные места. Поэтому в настоящее время осуществляется подготовка и оформление документов на ввод в эксплуатацию новой территории кладбища «Новое Стригинское». Мы стараемся, как можно раньше завершить эту процедуру, чтобы погост продолжал функционировать без неудобств для горожан», - рассказал начальник управления по благоустройству Виталий Ковалев.</p>\r\n', 0, '', '', '', 1365537600),
	(6, 2, 'article-1', 'Правила творчества от Милтона Глейзера', NULL, '<p>\r\n	Милтон Глейзер (Milton Glazer)&nbsp;— один из&nbsp;самых известных дизайнеров США, художник, который создал огромное количество шедевров графического дизайна, в&nbsp;том числе знаменитый логотип I ♥ NY. Помимо практики,&nbsp;<nobr>80-ти</nobr>&nbsp;летний Глейзер занимается и&nbsp;теорией: читает лекции в&nbsp;университетах, пишет эссе и&nbsp;теоретические рассуждения о&nbsp;связи дизайна и&nbsp;искусства и&nbsp;их&nbsp;влиянии друг на&nbsp;друга, о&nbsp;профессии дизайнера и&nbsp;сложностях творческого процесса.</p>\r\n<p>\r\n	Его речь «Ten Things I&nbsp;Have Learned» (10&nbsp;вещей, которым я&nbsp;научился), которую мы&nbsp;вам представляем, стала знаковой в&nbsp;мире творчества и&nbsp;разошлась на&nbsp;цитаты.<br />\r\n	 </p>\r\n<h3>\r\n	Работайте только с&nbsp;теми, кто вам нравится.</h3>\r\n<p>\r\n	У&nbsp;меня ушло много времени на&nbsp;то, чтобы сформулировать для себя это правило. Когда я&nbsp;начинал, думал наоборот, что профессионализм ― это умение работать с&nbsp;любыми клиентами. Но&nbsp;с&nbsp;годами стало понятно, что самый осмысленный и&nbsp;хороший дизайн был придуман для тех заказчиков, которые в&nbsp;последствии стали моими друзьями. Похоже, что симпатия в&nbsp;нашей работе гораздо важнее профессионализма.</p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Если у&nbsp;вас есть выбор, не&nbsp;устраивайтесь на&nbsp;работу.</h3>\r\n<p>\r\n	Однажды я&nbsp;услышал интервью с&nbsp;замечательным композитором и&nbsp;философом Джоном Кейджем. Ему было тогда 75. Ведущий спросил: «Как подготовиться к&nbsp;старости?». Я&nbsp;навсегда запомнил ответ Кейджа: «У&nbsp;меня есть один совет. Никогда не&nbsp;ходите на&nbsp;работу. Если в&nbsp;течение всей жизни вы&nbsp;каждый день ходите в&nbsp;офис, однажды вас выставят за&nbsp;дверь и&nbsp;отправят на&nbsp;пенсию. И&nbsp;тогда вы&nbsp;уж&nbsp;точно не&nbsp;будете готовы к&nbsp;старости ― она застанет вас врасплох. Посмотрите на&nbsp;меня. С&nbsp;12&nbsp;лет каждый день я&nbsp;делаю одно и&nbsp;то&nbsp;же: просыпаюсь и&nbsp;думаю о&nbsp;том, как заработать себе на&nbsp;кусок хлеба. Жизнь не&nbsp;изменилась после того, как я&nbsp;стал стариком».</p>\r\n<h3>\r\n	<br />\r\n	Избегайте неприятных людей.</h3>\r\n<p>\r\n	Это продолжение первого правила. В&nbsp;<nobr>60-х</nobr>&nbsp;работал гештальт-терапевт по&nbsp;имени Фритц Перлс. Он&nbsp;предположил, что во&nbsp;всех отношениях люди либо отравляют жизнь друг друга, либо подпитывают&nbsp;ее. При этом все зависит не&nbsp;от&nbsp;человека, а&nbsp;именно от&nbsp;отношений. Знаете, есть простой способ проверить, как влияет на&nbsp;вас общение с&nbsp;кем-нибудь. Проведите с&nbsp;этим человеком некоторое время, поужинайте, прогуляйтесь или выпейте вместе по&nbsp;стаканчику чего-нибудь. И&nbsp;обратите внимание на&nbsp;то, как вы&nbsp;будете чувствовать себя после этой встречи ― воодушевленным или уставшим. И&nbsp;все станет понятно. Всегда используйте этот тест.</p>\r\n<h3>\r\n	Опыта недостаточно.</h3>\r\n<p>\r\n	Я&nbsp;уже говорил, что в&nbsp;молодости был одержим профессионализмом. На&nbsp;самом&nbsp;же деле опыт ― это еще одно ограничение. Если вам нужен хирург, вы&nbsp;скорее всего обратитесь к&nbsp;тому, кто делал именно эту операцию много раз. Вам не&nbsp;нужен человек, который намерен изобрести новый способ пользоваться скальпелем. Нет-нет, никаких экспериментов, пожалуйста, делайте все так, как вы&nbsp;делали это всегда и&nbsp;как делали до&nbsp;вас. В&nbsp;нашей работе все наоборот ― хорош тот, кто не&nbsp;повторяет за&nbsp;собой и&nbsp;другими. Хороший дизайнер каждый раз хочет пользоваться скальпелем по-новому или вообще решает вместо скальпеля использовать садовую лейку.</p>\r\n<h3>\r\n	<br />\r\n	Меньше не&nbsp;всегда лучше.</h3>\r\n<p>\r\n	Все мы&nbsp;слышали выражение «лучше меньше, но&nbsp;лучше». Это не&nbsp;совсем правда. Одним прекрасным утром я&nbsp;проснулся и&nbsp;понял, что это скорее всего полная чушь. Оно, может быть, звучит неплохо, но&nbsp;в&nbsp;нашей области этот парадокс не&nbsp;имеет смысла. Вспомните персидские ковры, разве в&nbsp;их&nbsp;случае «меньше» было&nbsp;бы лучше? То&nbsp;же самое можно сказать про работы Гауди, стиль арт-нуво и&nbsp;много что еще. Поэтому я&nbsp;сформулировал для себя новое правило: лучше, когда всего ровно столько, сколько нужно.</p>\r\n<h3>\r\n	<br />\r\n	Образ жизни меняет образ мышления.</h3>\r\n<p>\r\n	Мозг ― самый чуткий орган в&nbsp;нашем теле. Он&nbsp;наиболее подвержен изменениям и&nbsp;регенерации. Несколько лет назад в&nbsp;газетах мелькала любопытная история про поиски абсолютного слуха. Как известно, встречается редко даже среди музыкантов. Не&nbsp;знаю как, но&nbsp;ученые выяснили, что у&nbsp;людей с&nbsp;абсолютным слухом другое строение мозга ― каким-то образом деформированы некоторые доли. Это уже интересно. Но&nbsp;потом они выяснили еще более интересную вещь ― если детей&nbsp;<nobr>4-5</nobr>&nbsp;лет учить игре на&nbsp;скрипке, есть вероятность, что у&nbsp;кого-нибудь из&nbsp;них начнет меняться строение коры головного мозга и&nbsp;разовьется абсолютный слух. Я&nbsp;уверен, что рисование влияет на&nbsp;мозг не&nbsp;меньше, чем занятия музыкой. Но&nbsp;вместо слуха мы&nbsp;развиваем внимание. Человек, который рисует, обращает внимание на&nbsp;то, что происходит вокруг него ― а&nbsp;это не&nbsp;так просто, как кажется.</p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Сомнения лучше, чем уверенность.</h3>\r\n<p>\r\n	Все говорят о&nbsp;том, как важна уверенность в&nbsp;том, что ты&nbsp;делаешь. Мой преподаватель по&nbsp;йоге как-то сказал: «Если вам кажется, что вы&nbsp;достигли просветления, вы&nbsp;всего лишь уперлись в&nbsp;собственные рамки». Это верно не&nbsp;только в&nbsp;духовной практике, но&nbsp;и&nbsp;в&nbsp;работе. Для меня все сформировавшиеся идеологические позиции ― сомнительны, потому что совершенство ― это бесконечное развитие. Я&nbsp;нервничаю, когда кто-то верит во&nbsp;что-то безапелляционно. Надо подвергать сомнению все. У&nbsp;дизайнеров эта проблема появляется еще в&nbsp;арт-школе, где нас учат теории авангарда и&nbsp;тому, что личность может изменить мир. В&nbsp;каком-то смысле это правда, но&nbsp;чаще всего заканчивается большими проблемами с&nbsp;творческой самооценкой. Коммерческое сотрудничество с&nbsp;кем-то ― это всегда компромисс, и&nbsp;этому ни&nbsp;в&nbsp;коем случае нельзя сопротивляться. Всегда надо допускать возможность того, что ваш оппонент может быть прав. Пару лет назад я&nbsp;прочел у&nbsp;Айрис Мердок лучшее определение любви: «любовь ― это крайне сложное осознание того, что кто-то кроме нас реален». Это гениально, и&nbsp;описывает абсолютно любое сосуществования двух людей.</p>\r\n<h3>\r\n	<br />\r\n	Это неважно.</h3>\r\n<p>\r\n	Однажды на&nbsp;День рождения мне подарили книгу Роджера Росенблатта «Старейте красиво». Название мне не&nbsp;понравилось, но&nbsp;содержание заинтересовало. Самое первое правило, предлагаемое автором, оказалось лучшим. Оно звучит так: «это неважно». Неважно, что вы&nbsp;думаете ― следуйте этому правилу и&nbsp;оно прибавит к&nbsp;вашей жизни десятилетия. Неважно, опаздываете вы&nbsp;или приходите вовремя, тут вы&nbsp;или там, промолчали&nbsp;ли вы&nbsp;или сказали, умны вы&nbsp;или глупы. Неважно, какая у&nbsp;вас прическа и&nbsp;как на&nbsp;вас смотрят ваши знакомые. Повысят&nbsp;ли вас на&nbsp;работе, купите&nbsp;ли вы&nbsp;новый дом, получите&nbsp;ли Нобелевскую премию ― все это неважно. Вот это мудрость!</p>\r\n<h3>\r\n	Не&nbsp;доверяйте стилям.</h3>\r\n<p>\r\n	Стиль ― понятие неактуальное. Глупо отдавать себя какому-то одному стилю, потому что ни&nbsp;один из&nbsp;них не&nbsp;заслуживает вас целиком. Для дизайнеров старой закалки это всегда было проблемой, потому что с&nbsp;годами вырабатывается почерк и&nbsp;визуальный вокабуляр. Но&nbsp;мода на&nbsp;стили опосредованна экономическими показателями, то&nbsp;есть практически как с&nbsp;математикой ― с&nbsp;ней не&nbsp;поспоришь. Об&nbsp;этом писал еще Маркс. Поэтому каждые десять лет сменяется стилистическая парадигма, и&nbsp;вещи начинают выглядеть иначе. То&nbsp;же самое происходит со&nbsp;шрифтами и&nbsp;графикой. Если вы&nbsp;планируете просуществовать в&nbsp;профессии больше одного десятилетия, очень рекомендую быть готовым к&nbsp;этой смене.</p>\r\n<h3>\r\n	Говорите правду.</h3>\r\n<p>\r\n	Иногда мне кажется, что дизайн ― не&nbsp;лучшее место для поисков правды. Безусловно в&nbsp;нашем сообществе существует определенная этика, которая, кажется, где-то даже записана. Но&nbsp;в&nbsp;ней нет ни&nbsp;слова об&nbsp;отношениях дизайнера с&nbsp;обществом. Говорят, 50&nbsp;лет назад все что продавалось с&nbsp;этикеткой «телятина», на&nbsp;самом деле было курицей. С&nbsp;тех пор меня мучает вопрос, что&nbsp;же тогда продавалось, как курица? Дизайнеры несут не&nbsp;меньше ответственности, чем мясники. Поэтому должны внимательно относится к&nbsp;тому, что они продают людям под видом телятины. У&nbsp;врачей есть клятва «Не&nbsp;навреди». Я&nbsp;считаю, что у&nbsp;дизайнеров тоже должна быть своя клятва ― «Говори правду».</p>\r\n', 0, '', '', '', 1365710400),
	(7, 2, 'article-2', 'Искусство на грани', NULL, '<p>\r\n	Ребята, ничего личного, но&nbsp;очень часто эти слова как нельзя лучше характеризуют творчество представителей этого самого современного искусства. В&nbsp;наш век информации и&nbsp;вседоступности удивить, впечатлить и&nbsp;заставить о&nbsp;себе говорить искушенную публику&nbsp;— дорогого стоит, а&nbsp;потому современные художники, пусть и&nbsp;не&nbsp;все, в&nbsp;погоне за&nbsp;популярностью занялись элементарным эпатажем. Не&nbsp;можешь отрисовать гениально и&nbsp;чтоб мурашки по&nbsp;коже, разденься до&nbsp;гола и&nbsp;просиди в&nbsp;каком-нибудь бункере под пристальными взглядами журналистов. И&nbsp;можно даже ничего не&nbsp;объяснять: люди сами придумают концепцию твоего поведения, и&nbsp;будут рассказывать об&nbsp;этом благоговейным шепотом.</p>\r\n<p>\r\n	Все-таки есть грань, отделяющая настоящее искусство от&nbsp;того, что создано с&nbsp;целью прославиться. В&nbsp;этом материале мы&nbsp;решили собрать неоднозначные объекты современного искусства, которые находятся на&nbsp;этой грани, а&nbsp;к&nbsp;чему они относятся&nbsp;— решите для себя сами.</p>\r\n<h3>\r\n	 </h3>\r\n<h3>\r\n	Мэтью Барни, видеокадр из&nbsp;перформанса «Кремастер-1»</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7585905/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7585905-R3L8T8D-600-z_a29ec5bd.jpg" /></a>В&nbsp;возрасте 24&nbsp;лет Барни объявился на&nbsp;нью-йоркской сцене с&nbsp;видеоперформансом «Порог в&nbsp;милю высотой: Полёт с&nbsp;анально-садистским воином». Барни в&nbsp;купальной шапочке, в&nbsp;туфлях на&nbsp;высоких каблуках и&nbsp;с&nbsp;титановым ледорубом в&nbsp;анусе лез по&nbsp;потолку галереи, в&nbsp;течении трёх часов сопротивляясь силам тяготения и&nbsp;дискомфорта.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Роберт Гобер «Человек выходит из&nbsp;женщины»</h3>\r\n<h3>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7590255/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7590255-R3L8T8D-600-20120827_R_Gober_untitled_ManComingOut.jpg" /></a></h3>\r\n<h3>\r\n	Гобер лепит из&nbsp;воска различные части тела и&nbsp;в&nbsp;самых неожиданных позах раскладывает их&nbsp;по&nbsp;галерее. Последние, порой декорированные свечами, порой пронзённые пластиковыми дренажными трубками, говорят об&nbsp;уязвимости человеческого тела и&nbsp;о&nbsp;его попранном положении.</h3>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Сара Лукас</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7591305/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7591305-R3L8T8D-600-08-4_lucas_lg.jpg" /></a><a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7592855/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7592855-R3L8T8D-600-5014052269_f85617cf43_z.jpg" /></a></p>\r\n<p>\r\n	Творческие работы Сары Лукас (Sarah Lucas) появлялись в&nbsp;самых значимых журналах и&nbsp;музеях современного британского искусства. По&nbsp;её&nbsp;словам, на&nbsp;фотографиях, коллажах, скульптурах, найденных объектах, инсталляциях и&nbsp;рисунках она рассуждает на&nbsp;темы сексуальных отношений, разрушения и&nbsp;смерти.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Рой Ваара «Человек с&nbsp;третьей ногой»</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7593055/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7593055-R3L8T8D-600-roi_collage.jpg" /></a>Рой Ваара (Rot Vaara)&nbsp;— самый знаменитый современный художник Финляндии, преподаватель университетов США и&nbsp;Европы. Этот человек, критикующий искусство за&nbsp;излишнюю музейность, объявил себя живущим арт-объектом ещё в&nbsp;1982&nbsp;году. С&nbsp;тех пор он&nbsp;и&nbsp;его третья нога сделали 300 уникальных перформансов и&nbsp;приняли участие в&nbsp;200 фестивалях в&nbsp;30&nbsp;странах мира.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Братья Джейк и&nbsp;Динос Чепмен.</h3>\r\n<h3>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7593405/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7593405-R3L8T8D-600-z_cd43377b.jpg" /></a></h3>\r\n<h3>\r\n	Скандальные художники из&nbsp;Британия братья Чепмен вообще прославились благодаря своим многочисленным провокациям, дракам и&nbsp;излюбленной темой половых органов, которые они с&nbsp;одинаковым усердием лепят и&nbsp;на&nbsp;картины старых мастеров, и&nbsp;на&nbsp;носы детям. Эта самая известная их&nbsp;работа представляет из&nbsp;себя&nbsp;удовищно сплавленное воедино кольцо из&nbsp;голых девочек-манекенов в&nbsp;кроссовках с&nbsp;вагинами вместо ртов и&nbsp;ушей или пенисами вместо носа.</h3>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Kaarina Kaikkonen</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7593555/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7593555-R3L8T8D-600-553.jpg" /></a><a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7593505/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7593505-R3L8T8D-600-14.jpg" /></a></p>\r\n<p>\r\n	С&nbsp;первого взгляда может показаться, что добрая половина мужского населения небольшого городка решила высушить одежду. Но&nbsp;нет&nbsp;— это арт-инсталляции финской художницы Kaarina Kaikkonen. Художница тем и&nbsp;прославилась, что научилась аккуратно развешивать разного рода бельишко и&nbsp;размещать все это под разными углами в&nbsp;городской среде. По&nbsp;её&nbsp;словам Kaikkonen, так она хочет понять, где начинается конец всей реальности.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Маурицио Каттелан</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7594005/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7594005-R3L8T8D-600-catt.jpg" /></a></p>\r\n<p>\r\n	Каттелану 50&nbsp;лет, но&nbsp;он&nbsp;до&nbsp;сих пор предпочитает называть своей главной отличительной чертой идиотизм. В&nbsp;прошлом почтальон, уборщик, повар и&nbsp;донор банка спермы, Каттелан, по&nbsp;собственному утверждению, работал для единственной цели&nbsp;— выжить. В&nbsp;опубликованном в&nbsp;британской газете «Гардиан» интервью Каттелан поведал, в&nbsp;частности, о&nbsp;том, что творит «не&nbsp;головой, а&nbsp;желудком», и&nbsp;потому не&nbsp;в&nbsp;состоянии объяснить смысл своих произведений.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Марк Дженкинс</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7594755/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7594755-R3L8T8D-600-1268285167_installations_by_mark_jenkins_50.jpg" /></a></p>\r\n<p>\r\n	Вообще Марк Дженкинс знаменит своими стрит-арт объектами, которые представляют из&nbsp;себя человеческие скульптуры, выполненные из&nbsp;скотча и&nbsp;поэлитилена, которыми он&nbsp;уже давно шокирует жителей Вашингтона и&nbsp;других городов. Также он&nbsp;периодически устраивает вот такие арт-инсталляции. Интересно и&nbsp;то, что художник создал пошаговую инструкцию об&nbsp;изготовлении скульптур из&nbsp;пленки, и&nbsp;проводит мастер-классы в&nbsp;городах, которые он&nbsp;посещает.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Saara Ekstr&#246;m</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7595155/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7595155-R3L8T8D-600-QrENUl2qKdQ.jpg" /></a></p>\r\n<p>\r\n	Ещё одна художница из&nbsp;Финляндии, чье творчество, мягко говоря, неоднозначно. Прославилась Saara Ekstr&#246;m своими инсталляциями, видео, фотографиями и&nbsp;рисунками. В&nbsp;своих работах она использует в&nbsp;качестве материала человеческие волосы, органы и&nbsp;туши животных и&nbsp;тому подобное. На&nbsp;одной из&nbsp;своих выставок финка даже представили татуированные куски кожи свиней. А&nbsp;ещё она преподает в&nbsp;университете.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Такаси Мураками «Мой одинокий ковбой»</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7597055/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7597055-R3L8T8D-600-D5w5KamfjDM.jpg" /></a>Мураками в&nbsp;Японии много, но&nbsp;такой только один. У&nbsp;него есть докторская степень Японского университета искусств, он&nbsp;один из&nbsp;наиболее успешных современных японских художников, а&nbsp;эта авторская скульптура «Мой одинокий ковбой» в&nbsp;2008 была продана на&nbsp;аукционе Sotheby за&nbsp;15&nbsp;миллионов долларов. Помимо этого Мураками рисует мультфильмы и&nbsp;создает детские игрушки.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Аурель Шмидт</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7598555/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7598555-R3L8T8D-500-tumblr_mdnr39nAWS1qz5g75o1_r3_500.jpg" /></a></p>\r\n<p>\r\n	Знакомьтесь, это&nbsp;— Аурель Шмидт из&nbsp;Нью-Йорка. Она, как вы&nbsp;уже догадались, художница, но&nbsp;немногие из&nbsp;вас признают произведениями искусства в&nbsp;её&nbsp;творениях. Юная леди занимается живописью, делает инсталляции из&nbsp;мусора, фотографирует, устраивает перфомансы с&nbsp;собственным участием, которые не&nbsp;обходятся без скандала. Примечательно, что авторитетный журнал Forbes как-то включил её&nbsp;в&nbsp;список самых успешных молодых людей.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Андрес Серрано</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7603505/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7603505-R3L8T8D-500-tumblr_m7cznxe8Sc1ry2rxso1_1280.jpg" /></a></p>\r\n<p>\r\n	Андрес Серрано (Andres Serrano) наполовину фотограф, наполовину гондурасцец, все делал правильно&nbsp;— фотографировал сексуальные сцены, трупы, испражняющихся людей, извращения, но&nbsp;успех к&nbsp;нему никак не&nbsp;приходил. Серрано стал звездой после того, как сделал фотографию «<a href="http://bigpicture.ru/wp-content/uploads/2009/11/1621-677x990.jpg" target="_blank">Piss Christ</a>» («Писающий Христос»). На&nbsp;снимке изображалось распятие, погруженное в&nbsp;мочу фотографа. В&nbsp;художественном плане снимок ничем не&nbsp;выделяется из&nbsp;прочих работ Серрано, но&nbsp;людей зацепило. Из&nbsp;никому не&nbsp;интересного фотографа он&nbsp;стал мировой знаменитостью, получил многочисленные призы, а&nbsp;фотография участвовала в&nbsp;выставках по&nbsp;всему миру. Несколько раз ее&nbsp;пытались уничтожить разгневанные посетители, в&nbsp;связи с&nbsp;этим постоянно росла страховая стоимость снимка и&nbsp;известность фотографа.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Дитер Рот «Гегель, собрание сочинений в&nbsp;20&nbsp;томах»</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7606005/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7606005-R3L8T8D-500-13fCBAaHyOw.jpg" /></a>Кто такой был Дитер Рот? Художник-абсурдист. Искусство Дитера Рота пачкается, воняет и&nbsp;медленно разрушается, и&nbsp;он&nbsp;считал, что так оно и&nbsp;должно быть. Дитер Рот, например, много работал с&nbsp;колбасой. Он&nbsp;измельчил в&nbsp;крошку&nbsp;<nobr>20-томное</nobr>&nbsp;собрание сочинений философа Гегеля, смешал бумагу с&nbsp;салом и&nbsp;сделал колбасу. 20&nbsp;батонов висят в&nbsp;два ряда, внутри колбасы хорошо видны буквы. Поглошение знаний в&nbsp;буквальном смысле слова.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<h3>\r\n	Линда Бенглис</h3>\r\n<p>\r\n	<a href="http://www.adme.ru/articles/iskusstvo-na-grani-484855/484855-7606705/" target="_blank"><img border="0" src="http://files.adme.ru/files/news/part_48/484855/7606705-R3L8T8D-500-z_ddaec8f9.jpg" /></a>От&nbsp;автора: «Большинство моих работ вызывает ощущение физического движения, словно это тело или что-то, побуждающее физиологический отклик...(к примеру), полиуретановые скульптуры наводят на&nbsp;мысль о&nbsp;каких-то волновых образованиях или животных внутренностях, они вызывают чувства, некоторым образом знакомые зрителю, природные чувства...иначе говоря, доисторические. Хотя формы не&nbsp;являются специфически узнаваемыми, чувства&nbsp;— являются». Без комментариев.</p>\r\n', 0, '', '', '', 0),
	(8, 2, 'article-3', 'Два дня в Red Pepper', NULL, '<p>\r\n	Red Pepper - еще одно уникальное порождение загадочной екатеринбуржской атмосферы. Самое раздолбайское агентство страны, которое при этом умудряется успешно работать.</p>\r\n<p>\r\n	Редактор AdMe.ru Ксения Лукичева съездила в гости к "красным перцам" (юридическое название - ООО "Абсолютная власть") с целью разобраться, как же у них это получается.</p>\r\n<p>\r\n	В те два дня, когда я приехала в Red Pepper, в гостях у них была не только я, но и татуировщик, расположившийся в кабинете генерального и креативного директора Данила Голованова и делавший татуировки всем желающим сотрудникам.&nbsp;Кто-то в процессе умудрялся работать, печатая одной рукой письма в то время, когда машинка жужжала над второй рукой, а кто-то давал интервью.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563855-R3L8T8D-600-IMG_1462.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	Данил Голованов решил совместить приятное и болезненное с разговором со мной, для полноты ощущений. Мы беседовали под жужжание машинки, пока мастер выводил на его загривке черные буквы "Made in Ural". В Екатеринбурге все, кого я знаю, отчаянные патриоты Урала, гордятся своим происхождением и носят этот "бренд" кто просто в сердце, а кто теперь и на коже.</p>\r\n<p>\r\n	- Я иногда думаю о том, что под каждое агентство можно подобрать песню или группу, которая будет целиком выражать суть этого агентства.</p>\r\n<p>\r\n	- Да? Например?</p>\r\n<p>\r\n	- Ну вот Восход, например, это Depeche Mode, - не задумываясь, выдает Даня. - Знаешь, такой… Любая песня из сборника "The Best Of".</p>\r\n<p>\r\n	- А StreetArt?</p>\r\n<p>\r\n	- StreetArt - мрачноватые ребята. В себе такие. Поэтому Дельфин, наверное?</p>\r\n<p>\r\n	- А вы? - улыбаюсь я.</p>\r\n<p>\r\n	- Про нас есть отдельный трек. Вот очень про нас. Justise, которая "We! Are! Your friends! You never be alone again, oh come on!", - бодро напевает Даня, шевеля только лицевыми мышцами, потому что иначе татуировка может приобрести ненужное направление.&nbsp;</p>\r\n<p>\r\n	Песню эту я, конечно, помню. Этакая смесь хипстоты с безудержным умением получать удовольствие от жизни. Очень про Ред Пеппер, факт.&nbsp;И вот под этот саундтрек мы и начинаем знакомство с агентством.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	В агентстве сейчас работает 25 человек в екатеринбургском офисе и четверо в Новосибирске. Половина из этих двадцати пяти сидит в кабинете, который проходит под названием "серьезный" - там медийщики, event и все такое прочее очень важное, но, по правде говоря, не особо фактурное. "Ты туда не ходи, скука смертная", говорит Даня, "тебе и нашего веселого офиса хватит".&nbsp;</p>\r\n<p>\r\n	Офис и впрямь не скучен. Например, центральное место в пестром интерьере с преобладающим красным занимает кровать-чердак, на которой можно поспать, и под которой находится "сиротское" рабочее место второго креативного директора агентства Никиты Харисова. Диван, на котором за день засиживаются все, кому не лень, и маленький икеевский придиванный столик.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563255-R3L8T8D-600-IMG_1436.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	Субординация в офисе присутствует только в одном случае - если Голованов уверен в своей правоте. Тогда он включает смесь своей внушительной харизмы и статуса владельца агентства, и коллеги, после жестокого спора, отступают.</p>\r\n<p>\r\n	- Ну да, - смущается Даня. - Иногда у меня бывают навязчивые идеи, и я их проталкиваю, давлю позицией. Бывает, что я оказываюсь прав, а бывает, что и нет.</p>\r\n<p>\r\n	Он кивает головой в сторону Харисова:</p>\r\n<p>\r\n	- Вот этот тип сейчас хочет снять абсолютно говеный ролик!</p>\r\n<p>\r\n	- А ты хочешь снять чертов ералаш! - не остается в долгу Харисов. И понеслась. Я настолько заворожена эмоциями, которыми они искрят и фонтанируют, что напрочь теряю нить и в итоге из-за спектакля пропускаю момент определения победителя.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563805-R3L8T8D-600-IMG_1451.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	Большие дети, занимающиеся рекламой, потому что им прикольно. Собравшие штат из самых разных людей, в основном не имеющих отношений к рекламе. Кладущие болт на субординацию, хохочущие над тем, что у них каждый третий - директор. Два креативных, коммерческий, финансовый, арт-, продакшен-, аккаунт- и медиа-директор. "30 процентов от штата! Больше, чем в Лео Бернетте, наверное", хохочет Харисов, но тут же делает серьезное лицо. "Но это больше влияет не на важность, а на степень ответственности. Этот статус, как ни крути, нужно отработать. Грубо говоря, ты не можешь прийти на работу с бодуна и весь день протупить, валяясь на кровати".</p>\r\n<p>\r\n	Харисов, недавно повышенный до креадира - известный в Екатеринбурге своими эммм… нестандартными вечеринками клубный промоутер и бармен с 10-летним стажем. В рекламу, как сам утверждает, попал случайно. Полтора года назад он участвовал в проекте для одного из ключевых клиентов, снял хороший ролик, и Голованов его пригласил в агентство попробовать заниматься видео. Видео потянуло за собой разработку концептов, копирайтерскую работу, стратегию и так далее. Универсальность и взаимозаменяемость - один из главных принципов агентства.</p>\r\n<p>\r\n	Джуниор-копирайтер Иван Соснин - когда мне его так представляют, я не удерживаюсь от ехидного замечания "Джуниор? Можно подумать, у вас есть другие копирайтеры" -&nbsp; на пятом курсе бросил теплофак, чтобы не дай бог не получить диплом и не работать потом по специальности. Ваня - очень любопытный персонаж. Весь, что называется, не от мира сего - смесь смущенного ботаника и чокнутого художника. Хотела сказать, что он вносит струю сумасшествия в агентство, но потом подумала, что этим занята половина Ред Пеппера во главе с Головановым. А еще Ваня снимает нечеловечски странные клипы, пугающие и завораживающие одновременно, самый понятный из которых -&nbsp;"Завтра" Сансары.&nbsp;</p>\r\n<p>\r\n	У них правда нет стратегов и копирайтеров, а арт-директором еще недавно был лидер группы "Сансара" Александр Гагарин.</p>\r\n<p>\r\n	И при всем при этом у них на годовых контрактах постоянно обслуживаются 3-4 крупных клиента на креативе и медиа, есть стабильный поток из "проектных" клиентов и есть несколько проектов, на которых, по словам Дани, они "ну прям деньги зарабатывают".&nbsp;И это не просто фирстиль и биллборды. Голованов особенно гордится тем, что они умеют работать с полной интеграцией проекта в бизнес. Это, конечно, в результате сказывается на том, что далеко не все можно оформить в красивый кейс, выставить на фестивали и послать на профильные сайты, но креативом можно блеснуть и на других проектах, а подобная работа добавляет +100 к самым разнообразным прикладным навыкам. Про выражение этих плюсов на банковских счетах тоже забывать не стоит.</p>\r\n<p>\r\n	Это все вовсе не значит, что у Ред Пеппера нет фестивальных амбиций - они есть, внушительные и одновременно какие-то дурашливые. Посылают работы и радуются, когда что-то выигрывают, не запариваются, когда пролетают мимо.</p>\r\n<p>\r\n	Об отношении к делу, о работе, о том, кем они хотят быть, когда вырастут, мы говорили с Данилом Головановым под жужжание татуировочной машинки.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563055-R3L8T8D-600-IMG_1431.JPG" /></p>\r\n<p>\r\n	<br />\r\n	- Даня, расскажи мне вот что. Я знаю тебя уже третий год, но не в курсе, откуда у тебя все это - свое агентство, городской сайт, два бара. Тебе же всего 26.</p>\r\n<p>\r\n	- В шутку все началось. Мы делали вечеринки в клубах, промоутерами были. В какой-то момент Даня Ерохин&nbsp;(партнер Голованова - прим. ред.)&nbsp;пришел и предложил открыть агентство, которое будет обслуживать пару магазинов одежды и один банк в плане эвентов. Ну мы и открыли&nbsp;(ухмыляется). Поработали года полтора - набирались опыта, делали какие-то абсолютно дурацкие ошибки, очень смешные. Случился кризис, и один из наших тогдашних клиентов, банк, попросил нас поразмещать ему наружку. Никто, кроме нас, не готов был делать это без предоплаты. Мы сказали клиенту, что не умеем, а он такой "Научитесь, там ничего сложного".</p>\r\n<p>\r\n	И сначала я лично размещал эту наружку первые два месяца, а потом пригласили человека, и так у нас появился медиа-отдел в агентстве из 3-4 человек. Через какое-то время мы стали заниматься креативом, и вот…</p>\r\n<p>\r\n	- Получается?</p>\r\n<p>\r\n	- Мы все хотим, конечно, пойти в сторону Восхода, Инстинкта - делать офигенные кампании задорого и специализироваться на креативе. Но это у нас пока получается тяжело - мало кто в состоянии дать агентству глобальные ролики, дорогие проекты с хорошим продакшеном. Поэтому нам приходится находить себе работу, которая больше интегрирована в бизнес клиентов. Наш проектный отдел делает все под ключ, это же огромный объем работ - мы заходим в проект на уровне финансового планирования, бизнес-плана, рентабельности, и очень часто бывает, что заработаем мы денег или нет зависит от того, насколько хорошо мы сделаем этот проект.</p>\r\n<p>\r\n	Еще делаем очень много такой работы, которую никому нельзя показывать, как и многим агентствам, которые с табачными компаниями работают. Но это вообще отдельная история.</p>\r\n<p>\r\n	- Этим у вас как раз тот кабинет серьезный занимается?</p>\r\n<p>\r\n	- Нет, тот кабинет занимается медийкой только и мероприятиями. Проектные менеджеры и аккаунты здесь сидят. У нас как вообще получается: все проекты поступают в креативный отдел, мы придумываем общее направление, пишем бриф для проектников, потом они уже по нему начинают работать. Причем это может быть абсолютно разного уровня креатив, от сценария видеоролика до разработки мобильных приложений, мы даже сделали социальную сеть для одного бренда. Еще с нами все подрядчики очень любят по мероприятиям работать.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563455-R3L8T8D-600-IMG_1438.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	- Как так получилось, что у вас сплошная молодежь, тебе 26 лет, вы вообще такое раздолбайское агентство, а ведете такие крупные проекты?</p>\r\n<p>\r\n	- Агентство раздолбайское до поры до времени. Мы же еще и деньги зарабатываем, зарплаты у нас… выше рыночных, я бы сказал. И у нас у всех есть очень четкое ощущение наступления пи*деца. И есть понимание, что любой проект зависит от каждого человека в агентстве, есть чувство ответственности. Поэтому мы собираемся в какой-то момент и выжимаем себя.</p>\r\n<p>\r\n	- Под дедлайн?</p>\r\n<p>\r\n	- Да, под дедлайн. У нас же нет специалистов. Первый специалист, которого мы пригласили, это Карюкин&nbsp;(Андрей Карюкин до Red Pepper проработал несколько лет сейлзом в "Восходе" - прим.ред.). До этого не было. И пока креативщикам и медийщикам тяжело с ним работать, у него свои стандарты, бюрократия… а у нас все так более свободно, в кайф.</p>\r\n<p>\r\n	- Представь себе, что твои сотрудники уходят в другое агентство. Какое это агентство могло бы быть из российских? Где бы они прижились лучше всего?</p>\r\n<p>\r\n	- Мне почему-то кажется, что Ред Кедс, Твига - по духу. Но по факту - скорей Leo Burnett.</p>\r\n<p>\r\n	- По какому такому факту?</p>\r\n<p>\r\n	- У нас есть опыт - и достаточно большой - в работе с определенным клиентом&nbsp;(очевидно, имеется в виду Philip Morris - прим. ред.). Все наши знают нормы, требования, документацию этого клиента, знают все пути рекламы для него, это уже готовые сотрудники под конкретных клиентов.</p>\r\n<p>\r\n	И это точно не Восход - мне Губайдуллин как-то говорил, что он никого бы из наших не взял&nbsp;(смеется)</p>\r\n<p>\r\n	- А кого бы из нынешних российских рекламщиков ты хотел бы видеть в своем агентстве? Даже если это мечты и совсем невозможно.</p>\r\n<p>\r\n	-&nbsp;(загибает пальцы)&nbsp;Ну… Примаченко, пол-Инстинкта, пол-Восхода, весь диджитал Ред Кедс, Кинограф бы взял в качестве продакшена&nbsp;(смеется). Грейт еще нравится в последнее время. Твига нравится. Но больше всего нравится Инстинкт. Они делают примерно то же самое, что и Восход, только для огромных брендов, а это же очень сложно, практически невозможно.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563555-R3L8T8D-600-IMG_1441.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	- Какие у вас цены по Екатеринбургу? Не самые высокие?</p>\r\n<p>\r\n	- Не. Ну смотря как оценивать. В плане организации эвентов и каких-то нестандартных механик, у нас точно самый высокий прайс. Мы не беремся за проекты, которые не соответствуют нашим внутренним понятиям о том, как оно должно быть, фигней не занимаемся.&nbsp;А по креативу у Восхода, конечно, самый большой.&nbsp; Мы, наверное, даже в тройку не входим.</p>\r\n<p>\r\n	- Где вы учитесь?</p>\r\n<p>\r\n	- Нас клиенты учат.</p>\r\n<p>\r\n	И мы хотим видепродакшен развивать - ролики-ролики-ролики-ролики-ролики-ролики… Очень много идей, снимаем кучу роликов, которые в итоге не показываем никому, потому что качество плохое. Здесь в Екатеринбурге никто, кроме Губайдуллина, как мне кажется, снимать не умеет. А Губайдуллина на всех не хватит - ему некогда, у него своя работа.</p>\r\n<p>\r\n	Если разобраться - вроде бы ничего сложного, но сами так не можем пока.</p>\r\n<p>\r\n	Еще хотим развивать айдентику. Набрали очень много талантливых дизайнеров. Вот Ирина, арт-директор наш, она талантливая очень, безумно работоспособная, и при этом это человек, который спокойно с первого раза делает макеты для федеральных кампаний международных клиентов. И все всегда очень довольны ее работой. Она знает, что нужно клиенту.</p>\r\n<p>\r\n	Я думаю, что в этом году у нас будет упор именно на рекламную рекламу. Не на маркетинг, как было раньше. Это очень легко оказалось - в рейтинге АКАРа в списке маркетинговых сервисов мы на втором месте. Представляешь?</p>\r\n<p>\r\n	- Ну потому что этим обычно очень тихо занимаются.</p>\r\n<p>\r\n	- Ну да&nbsp;(смеется). Обычно это еще и неинтересно бывает. А у нас идей много. Главное - придумать оригинальную коммуникацию с потребителем. У нас был директ для табачников, когда мы выбирали бренд-амбассадора. Привезли в город две феррари, девочки знакомились с парнями в соцсетях, договаривались встретиться где-то в центре города. Они подъезжали на феррари, парни садились в машину, им надевали на головы пакеты, увозили в театру драмы, заводили с черного входа, снимали пакеты, и они оказывались в очень ярком освещении, где сидело очень много людей, и у них брали интервью, способен ли он работать бренд-амбассадором или нет. Заставляли их петь, танцевать стриптиз, что угодно. Знаешь, если бы это был не табачный клиент, можно было бы такой кейс сделать сумасшедший… У некоторых людей истерика прям реально была, некоторые удивляли идеями еще больше, чем мы сами. Интересно было.</p>\r\n<p>\r\n	Я люблю такое.&nbsp;Как Duval Guillaume делает. Кто бы что ни говорил про то, что это фейк, но когда видео набирает такое количество просмотров и становится вирусом, когда оно попадают в топ-20 видео Ютуба, я считаю, что это прямо такая документальная реклама получается. Потому что по сути они снимают просто документальные короткометражки про розыгрыши, но при этом находят офигенный инсайт для бренда и очень грамотно все монтируют. В этом году мы что-нибудь такое обязательно сделаем прикольное. Есть мысли...</p>\r\n<p>\r\n	А еще мне&nbsp;Вайден&nbsp;нравится.</p>\r\n<p>\r\n	- А кому не нравится Вайден?</p>\r\n<p>\r\n	- Очень много кому.</p>\r\n<p>\r\n	- Да ладно?</p>\r\n<p>\r\n	- Ну, допустим, многие считают Вайден агентством, которое очень сильно переигрывает. Увлекается. Что они отходят от сути. Я тебе сейчас объясню, что я имею в виду. Они делают рекламу будущего, которая зачастую кажется непонятным не только потребителям, но и даже некоторым моим коллегам. У нас был очень долгий разговор с кем-то из наших рекламщиков про&nbsp;Heineken "Entrance", они говорили, что они понимают, насколько это все красиво и круто, но не понимают, что именно этим роликом хотел сказать сам бренд. Я говорил им, что это искусство, кино… Может завидуют просто, не знаю.</p>\r\n<p>\r\n	- Как им можно завидовать, если они лучше всех? Зависть объяснима, когда она к соседу, который более успешен, чем ты, и лучше живет. А это же верхняя планка.</p>\r\n<p>\r\n	- Хорошо, что в то время, когда Восход поднимался, мы еще всем этим не так увлекались. Мы бы тоже завидовали&nbsp;(смеется)&nbsp;А так получилось, что они помогли нам вырасти. Они помогли, помогла Идея, когда мы второй раз туда приехали. Уверенность в своих силах почувствовали. Щас мы хотим, конечно, в плане фестивалей международную награду получить, потому что у нас их пока почти нет.</p>\r\n<p>\r\n	- А что ты хочешь?</p>\r\n<p>\r\n	- Я хочу Канны, конечно. Евробест, какой-нибудь крутой новый фестиваль типа PIAF. У нас есть там шорты, но я хочу золото.</p>\r\n<p>\r\n	Хочется просто сделать рекламу, которую будут смотреть десятки миллионов людей. Хочется сделать по-настоящему крутую социалку, которая будет не просто красивая и трендовая, а которая будет работать, как Восход. По сути это первая социалка, которая сработала в России, потому что до этого…&nbsp;(морщится, задумывается)</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563405-R3L8T8D-600-IMG_1449.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	Знаешь, мы в рекламу играем. Для нас это не работа, это образ жизни такой. Наверное, это и есть один из трендов современного рынка рекламного, что можно вот так вот взять, пойти и начать делать что-то новое, чего раньше никто не делал. То, что сейчас происходит в российском профессиональном сообществе, это… Это очень узко, такая старая тусовка со своими правилами, законами, которым не нравится, что все вокруг меняется. Поэтому Восход вырос, потому что они готовы были к переменам. Поэтому мы появились, потому что мы не заморачиваемся на важности.</p>\r\n<p>\r\n	Если есть азарт, какая-то идейная молодость у агентства, у конкретных людей, то это очень много значит. И главное - нельзя стесняться того, что делает агентство. У меня есть много знакомых из сетевых агентств, которые свои работы вообще не показывают.</p>\r\n<p>\r\n	- Стыдно им.</p>\r\n<p>\r\n	- У них есть комплекс названия, аббревиатуры, которая висит над ними, а у нас нет, нам в этом плане проще. Мы, конечно, тоже не показываем всего, что мы делаем, потому что иногда делаем совсем говно. Но это все равно шаг вперед хоть какой-то. Я даже лекцию как-то читал, где показывал только говно, которые мы сделали. Кейсы нормальные и так везде можно в сети посмотреть, а говна не найдешь, все прячут, стесняются. Лекция на ура, люди прямо ржали над нами.</p>\r\n<p>\r\n	- У вас юрлицо называется ООО "Абсолютная власть", это круто.</p>\r\n<p>\r\n	- Да. Это все само рождается, эти шутки все. Просто надо легче ко всему относиться.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563955-R3L8T8D-600-IMG_1473.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	Никита Харисов, уже описанный ранее второй креативный директор с сиротским рабочим местом и аж двумя новыми, саднящими татуировками, сделанными в кабинете у Голованова без отрыва от производства, еще сам не до конца понял, как его занесло в рекламу и что он тут делает. Днем - рекламист, ночью - клубный промоутер, известный трэш-вечеринками. Описание такое, что не сразу заподозришь, что человек в действительности уже во все врубился и куда успешней многих, кто настойчиво идет в рекламу годами.</p>\r\n<p>\r\n	Харисов воспринимает рекламу не настолько серьезно, она не стоит у него на золотом пьедестале, а в красном углу нет иконостаса. Это работа и веселье, это то, что позволяет ему фантазировать и решать интересные задачи. Еще один необычный персонаж екатеринбургской рекламы.</p>\r\n<p>\r\n	<br />\r\n	- У меня нет никаких амбиций по завоеванию мирового рынка или там фестивальных наград. Я просто люблю придумывать, фантазировать. А эта работа дает еще возможность все это структурировать и доносить до публики правильным месседжем.</p>\r\n<p>\r\n	- Что доносить? Твои фантазии?</p>\r\n<p>\r\n	- Мои фантазии, то, что я хочу сказать. Это учит тебя разговаривать с аудиторией. Приходит клиент, и тебе интересно, как он будет говорить со своей аудиторией. Грубо говоря, ты являешься языком и ртом клиента. От тебя очень много зависит, и это такая… власть…</p>\r\n<p>\r\n	- ООО "Абсолютная власть", ага.</p>\r\n<p>\r\n	-&nbsp;(хохочет)&nbsp;Да. Наверное, поэтому мы так и называемся.</p>\r\n<p>\r\n	- Твои фантазии же, получается, ограничены очень сильно тем, что хочет клиент, тем, что он не принимает что-то, выкидывает самое интересное на твой взгляд.</p>\r\n<p>\r\n	- Чем больше ограничений, тем интересней работать. Когда у тебя полный полет, ты начинаешь просто придумывать трэш. Ведешь себя как художники-импрессионисты или представители современного искусства. Они просто делают непонятные штуки. А тут ты начинаешь задумываться об эффективности. Есть четкая задача, и ты рисуешь какую-то картину, которая должна передать определенную мысль тому, кто на нее смотрит.</p>\r\n<p>\r\n	- А на вечеринках своих ты уже как представитель современного искусства выступаешь?</p>\r\n<p>\r\n	- Да&nbsp;(смеется). На своих вечеринках я уже отрываюсь по полной. Восстанавливаю баланс.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563755-R3L8T8D-600-IMG_1456.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	- Многие в российской рекламе относятся к клиентам, как к мудакам. Как относятся к клиентам в Red Pepper?</p>\r\n<p>\r\n	- Я считаю точку зрения, что клиенты - мудаки,&nbsp;(задумывается)&nbsp;несколько необоснованной. Клиент - это тот, кто дает нам работу. мы не свободные художники, мы созданы для того, чтобы работать на кого-то. Конечно, бывают клиенты, которые чего-то не понимают. Или хотят слишком многого, но при этом ставят миллиард ограничений. Есть люди, которые видят картинку, которую они хотят, но при этом почему-то не хотят в этом признаваться, и только когда ты уже даешь ему продукт, он говорит: "нет, я хочу вот так, так и так". Ты спрашиваешь у него, как это должно выглядеть, а он: "Я не знаю! (хлопает глазами)&nbsp;Вы же рекламное агентство, вы же креативщики, вот и думайте".</p>\r\n<p>\r\n	Клиенты бывают разные, но с этим нужно просто смириться, нужно уметь убеждать, уметь аргументировать свою точку зрения. Хотя, конечно, не всегда это и получается.</p>\r\n<p>\r\n	- Ругаетесь с клиентами?</p>\r\n<p>\r\n	- Нет. Почти нет. Больше всего мы спорим между собой с Данилом. Хотя и спорить с ним все равно бесполезно&nbsp;(корчит мину), я пытаюсь до конца отстаивать свою точку зрения.</p>\r\n<p>\r\n	- А почему с ним бесполезно спорить?</p>\r\n<p>\r\n	- У Данила есть один мощный плюс: он умеет правильно и быстро подобрать аргументацию. У меня в этом пока такой небольшой затык, я не очень умею оперативно мыслить: мне нужно сесть одному, подумать в тишине минут 5-10, и тогда у меня что-то рождается.<br />\r\n	Поэтому у нас на встречах с клиентами я почти всегда молчу и слушаю, что-то себе на ус наматываю, а потом могу даже просто выйти из кабинета, и у меня уже родится мысль. Я считаю, что лучше все сначала обдумать, чем ляпать сразу.</p>\r\n<p>\r\n	- Используете какие-то традиционные методы придумывания идей типа мозговых штурмов? Как вообще у вас это происходит?</p>\r\n<p>\r\n	- Да по-разному. В основном у нас идеи три человека придумывают: я, Данил и Ваня Соснин. Обычно либо мы с Ваней сидим и обсуждаем все в диалоге, и у нас рождаются идеи, и потом уже одну дорабатывает Ваня, одну я. Либо каждый из нас вынашивает по одной идее, потом мы вместе собираемся, обсуждаем, находим плюсы и минусы, стараемся вместе доработать и превратить в конечный продукт.</p>\r\n<p>\r\n	- А Голованов как участвует?</p>\r\n<p>\r\n	- Бывает, что он звонит в два часа ночи с воплем "Аааа! Я придумал!!". Но обычно он тоже придумывает одну идею - мы же по три идеи клиенту сдаем. Критикуем идеи друг друга, говорим "О, вот это прикольно, вот здесь нужно доработать". То есть Ваня очень хорошо придумывает оболочку - внешние какие-то вещи, копирайты. Но у него бывает сложно с инсайтами. Слоган прикольный, визуал прикольный, но непонятно, что он хочет этим донести. А у меня наоборот. Мне проще находить инсайты и месседжи, но как все это обернуть - бывают проблемы. Сознание путанное у меня (смеется), поэтому работаем в тандеме.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563705-R3L8T8D-600-IMG_1460.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	- А почему тебе на фестивали пофиг?</p>\r\n<p>\r\n	- Я считаю, что лучшая награда - это, во-первых, эффективность рекламной кампании, а во-вторых, если простые люди заметят ее и будут обсуждать. Большинство работ на фестивалях, как мне кажется, это реклама ради рекламы, современное искусство. Мы что-то хотим сказать, но поймут это только рекламщики - идеи, инсайты, экзекьюшен. А простые люди, даже образованные, они же этого не видят, не знают всего этого.</p>\r\n<p>\r\n	- Какой работой своей ты гордишься? Чего ты прикольного придумал?</p>\r\n<p>\r\n	- Наверное, это больше не креатив, а разработка. Я вел этот проект от начала и до конца - приложение для одного табачного бренда. Я полностью продумал структуру, механику, дизайн мы вместе с Ириной делали. Наверное, вот это. Я считаю, что приложение имеет будущее, и с учетом того, например, что рекламы в табачной промышленности становится все меньше и меньше, и скоро все уйдет в мобильные приложения. Интернет-сайты - это, конечно, хорошо, но они не прикладные в основном, а мобильные приложения - прикладные и всегда с собой.</p>\r\n<p>\r\n	- Интересно. Обычно гордятся каким-нибудь креативом.</p>\r\n<p>\r\n	- Наверное, я просто еще не создал такой креатив, которым мог бы гордиться.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563505-R3L8T8D-600-IMG_1437.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	В Red Pepper всегда слышно Голованова, Харисова, Карюкина и проект-менеджера Артема Зверева. Они самые разговорчивые, вечно смеющиеся и громогласные. В эту канву вплетаются голоса других сотрудников, и только из одного угла офиса практически никогда никого не слышно - четыре компьютера, четыре человека сосредоточенно смотрят в монитор, щелкают клавиатурами и мышками. Это дизайнеры под предводительством арт-директора Ирины Коротич, и если абстрагироваться от всего и какое-то время понаблюдать за ними, то можно запросто впасть в медитативное состояние, а потом решить, что вот, похоже, только дизайнеры-то тут и работают.</p>\r\n<p>\r\n	Моя попытка отвлечь Иру на разговор в диктофон ни к чему не привела - Ира смеялась, сияла ямочками на щеках, односложно отвечала и все время посматривала на монитор. Очевидно, она нужна агентству как воздух - просто для баланса. Наш разговор прервал Голованов: "Ира - трудоголик, она не умеет отдыхать!" - "А ты - плохой начальник", парировала Ира. "Мне к вечеру вот макет нужен" - "Сделаем", кивает самый тихий и спокойный арт-директор в мире и уходит в работу.</p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/484305/7563605-R3L8T8D-600-IMG_1444.JPG" /></p>\r\n<p>\r\n	 </p>\r\n<p>\r\n	Вечером второго дня мы мигрируем из одного бара в другой, отмечая день рождения Андрея Карюкина, чья должность на визитках указана, как sales manager, но по сути он выполняет функции коммерческого директора ("у нас тут все директора", помните?).</p>\r\n<p>\r\n	Именинник, разговорившись, подвел прекрасный итог поездке и попыткам понять, как у Red Pepper получается вести успешный бизнес.</p>\r\n<p>\r\n	- Red Pepper - это just for fun. Большая такая семья, кореша, которые вместе делают бизнес. Агентство сейчас находится на стадии роста - приходят большие клиенты, большие бюджеты. Сейчас - тот переломный момент, после которого Red Pepper или подрастет и рестуктуризируется, или уйдет с рынка.</p>\r\n<p>\r\n	- Атмосферу сохранить не получится?</p>\r\n<p>\r\n	- Может и получится. Все от Дани зависит. Понимаешь, в бизнесе есть определенные правила игры, доказательная база, что вот так все и должно существовать и действовать, а он существованием Red Pepper всю эту базу на корню опровергает. Вот он утром приходит в офис, ему легко, весело и интересно. Он никого не дрючит, никого не песочит, ни на кого не орет, никаких протокольных вещей и процессов прописанных нет, но все работает. Как? Х*й знает.</p>\r\n', 0, '', '', '', NULL),
	(9, 2, 'article-4', 'Краудсорсинг в интернет-рекламе', NULL, '<p>\r\n	Crowdsourcing&nbsp;— решение задач силами множества добровольцев. В&nbsp;рекламе характеризуется организацией большого числа людей («crowd»&nbsp;— толпа) для реализации потребностей бренда. Толпа пребывает в&nbsp;состоянии восторга от&nbsp;возможности «привнести частичку себя» в&nbsp;общее большое дело.</p>\r\n<p>\r\n	Одними из&nbsp;первых crowdsourcing освоили коммунисты. С&nbsp;1919&nbsp;г. толпы пролетариев в&nbsp;едином порыве выходят на&nbsp;так называемые субботники.</p>\r\n<p>\r\n	Эмоциональный заряд большого труда обладает огромнейшей силой.</p>\r\n<p>\r\n	Вполне естественно, что&nbsp;<s>хитрые</s>&nbsp;мудрые рекламщики приспособили отточенный инструмент и&nbsp;для своих целей.</p>\r\n<p>\r\n	Только представьте себе, пришли вы&nbsp;в&nbsp;Starbucks, а&nbsp;там кофе, приготовленный по&nbsp;лично вашему рецепту! Более того, сей божественный напиток каждый день пьют миллионы людей! Сердце радуется!</p>\r\n<p>\r\n	Компания Starbucks находится на&nbsp;«острие атаки» crowdsourcing в&nbsp;большом бизнесе. Львиную долю публикаций в&nbsp;основном&nbsp;сообществе бренда&nbsp;на&nbsp;Facebook (&gt;34&nbsp;млн. likes), составляют посты про успехи проекта&nbsp;My&nbsp;Starbucks Idea. My&nbsp;Starbucks Idea&nbsp;— это сайт-агрегатор идей клиентов компании по&nbsp;категориям: расположение, технологии, рецепты, карточки и&nbsp;т.д.</p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/487355/7669505-R3L8T8D-600-2.jpg" /></p>\r\n<p>\r\n	Сейчас проекту ровно 5&nbsp;лет и&nbsp;1&nbsp;месяц, пользователи разместили на&nbsp;сайте 156&nbsp;482 идеи (84&nbsp;идеи/день). Реализовано 277. Неплохой результат, с&nbsp;учетом того, что сайт существует только на&nbsp;английском языке.</p>\r\n<p>\r\n	SAS</p>\r\n<p>\r\n	Похожий, но&nbsp;чуть менее масштабный, проект запустила авиакомпания SAS. Весной 2012&nbsp;г. стартовал&nbsp;My&nbsp;SAS Idea. Сайт, где пассажиры могут делиться своими идеями, как улучшить авиакомпанию: куда открыть рейс, какой будет дизайн кружек и&nbsp;т.д.</p>\r\n<p>\r\n	Пара примеров:</p>\r\n<p>\r\n	—&nbsp;С&nbsp;помощью сайта проекта авиакомпания определила новое направление. За&nbsp;одну неделю было предложено 180&nbsp;городов, ТОР 10&nbsp;выставлен на&nbsp;голосование в&nbsp;Facebook. Победила Анталия.</p>\r\n<p>\r\n	—&nbsp;В&nbsp;следующий раз на&nbsp;кону был дизайн используемых на&nbsp;борту стаканчиков. За&nbsp;неделю компания получила 750&nbsp;вариантов.</p>\r\n<p>\r\n	«My&nbsp;SAS Idea»... ничего вам не&nbsp;напоминает? Возможно, тремя параграфами выше, был проект «My&nbsp;Starbucks Idea»? Дальше&nbsp;— больше. Слоганы в&nbsp;студию:</p>\r\n<p>\r\n	—&nbsp;2012:&nbsp;My&nbsp;SAS&nbsp;Idea.&nbsp;Share.&nbsp;Vote. Comment.</p>\r\n<p>\r\n	—&nbsp;2008:&nbsp;My&nbsp;Starbucks&nbsp;Idea. Share. Vote.&nbsp;Discuss. See.</p>\r\n<p>\r\n	Становится понятно, чьим примером «вдохновлялся» SAS. Ну&nbsp;что&nbsp;же, великие умы думают одинаково.</p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/487355/7669455-R3L8T8D-600-3.jpg" /></p>\r\n<p>\r\n	Starbucks и&nbsp;SAS большие молодцы. Эти компании сумели превратить свою аудиторию в&nbsp;нескончаемый источник актуальных решений. Креативный ключик бьет на&nbsp;постоянной основе.</p>\r\n<p>\r\n	Есть несколько очень похожих на&nbsp;My blablabla Idea, проектов. Это: Dell и&nbsp;его сайт&nbsp;Idea Storm, норвежская финансовая группа DNB с&nbsp;проектом DNB Labs и&nbsp;др.</p>\r\n<p>\r\n	MacDonald’s</p>\r\n<p>\r\n	Конечно, не&nbsp;все готовы так далеко шагать. Есть превосходные примеры «местного применения» инструментария crowdsourcing. Один из&nbsp;таких, кампания «Mein Burger» («Мой бургер»), организованная MacDonald’s в&nbsp;Германии по&nbsp;случаю&nbsp;<nobr>40-летнего</nobr>&nbsp;юбилея.</p>\r\n<p>\r\n	MacDonald’s открыл сайт, где любители fast food могли сконструировать свой собственный бургер. А&nbsp;затем прорекламировать свое творение в&nbsp;соц. сети и&nbsp;даже offline.</p>\r\n<p>\r\n	Немецкие бюргеры так увлеклись бургеростроением, что&nbsp;рекламная кампания Mein Burger стала самой успешной за&nbsp;всю историю MacDonald’s!</p>\r\n<p>\r\n	Вот результаты:</p>\r\n<p>\r\n	—&nbsp;7&nbsp;миллионов посетителей страницы.</p>\r\n<p>\r\n	—&nbsp;116&nbsp;000 созданных бургеров в&nbsp;течение&nbsp;<nobr>5-ти</nobr>&nbsp;недель. Новый бургер рождался каждые 26&nbsp;секунд.</p>\r\n<p>\r\n	—&nbsp;12&nbsp;000 созданных рекламных кампаний.</p>\r\n<p>\r\n	—&nbsp;1,5 миллиона человек приняли участие в&nbsp;голосовании.</p>\r\n<p>\r\n	—&nbsp;17&nbsp;миллионов&nbsp;— общий охват рекламной кампании в&nbsp;интернете.&nbsp;Каждый&nbsp;<nobr>4-ый</nobr>&nbsp;интернет-пользователь Германии!</p>\r\n<p>\r\n	Победу одержал бургер «Pretzelnator», содержащий: ветчину, американский и&nbsp;итальянский сыр и&nbsp;булочку-претцель.</p>\r\n<p>\r\n	<img border="0" src="http://files.adme.ru/files/news/part_48/487355/7669605-R3L8T8D-600-4.png" /></p>\r\n<p>\r\n	Формула</p>\r\n<p>\r\n	Подозреваю, что нет единого рецепта создания успешных crowdsourcing кампании. Как мы&nbsp;убедились выше, «субботник» можно использовать и&nbsp;как основу инновационного процесса в&nbsp;компании (Starbucks) и&nbsp;как «препарат местного воздействия» (MacDonald’s).</p>\r\n<p>\r\n	Существует несколько простых рекомендаций, основанных на&nbsp;здравом смысле. Они помогут вам организовать эффективный crowdsourcing (подсмотрено здесь:&nbsp;5&nbsp;Tips for Crowdsourcing Your Next Marketing Campaign):</p>\r\n<p>\r\n	1)&nbsp;Ясно сформулируйте задачу.&nbsp;Например: сделай лучший бургер.</p>\r\n<p>\r\n	2)&nbsp;Предложите хороший стимул: приз, слава и&nbsp;т.д. Чем более любим ваш бренд среди покупателей, тем менее значим стимул. Starbucks особо не&nbsp;заморачивается J</p>\r\n<p>\r\n	3) Не&nbsp;перегружайте участников.</p>\r\n<p>\r\n	4)&nbsp;Будьте готовы к&nbsp;валу хороших идей и&nbsp;шлака.&nbsp;Значит нужно продумать систему отделения зерен от&nbsp;плевел.</p>\r\n<p>\r\n	5)&nbsp;Crowdsourcing НЕ&nbsp;означает «непрофессионально».&nbsp;Среди ваших клиентов вполне могут оказаться отличные дизайнеры, копирайтеры, повара и&nbsp;инженеры.</p>\r\n<p>\r\n	Применение crowdsourcing крайне положительно сказывается на&nbsp;имидже компании. Используйте crowdsourcing и&nbsp;ваши показатели involvement (вовлечения) взлетят до&nbsp;небес, а&nbsp;волосы бренд-менеджеров станут мягкими и&nbsp;шелковистыми J</p>\r\n<p>\r\n	А&nbsp;если серьезно, crowdsourcing, и&nbsp;правда, очень полезная стратегия. Вы&nbsp;сможете аккумулировать огромное количество идей, мнений, стать значительно ближе к&nbsp;клиенту. Даже если вы&nbsp;такой гигант как Starbucks, SAS, Dell или MacDonald’s.</p>\r\n<p>\r\n	Естественно, на&nbsp;словах все намного проще, чем на&nbsp;деле. Описанные проекты относятся к&nbsp;высшему пилотажу, для исполнения которого потребуется большое умение и&nbsp;управленческая воля...</p>\r\n<p>\r\n	Если вы&nbsp;знаете интересные примеры crowdsourcing кампаний, особенно в&nbsp;России, добро пожаловать в&nbsp;комментарии!</p>\r\n<p>\r\n	Автор: Сергей Кокарев, creator агентства&nbsp;Primax</p>\r\n', 0, '', '', '', 1365710400);
/*!40000 ALTER TABLE `article_ru` ENABLE KEYS */;

-- Дамп структуры для таблица cms.catalog_1
DROP TABLE IF EXISTS `catalog_1`;
CREATE TABLE IF NOT EXISTS `catalog_1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias` char(40) NOT NULL,
  `title` varchar(150) NOT NULL,
  `metaTitle` varchar(300) NOT NULL,
  `metaKeyword` varchar(300) NOT NULL,
  `metaDescription` varchar(300) NOT NULL,
  `genry` varchar(300) NOT NULL DEFAULT '',
  `year` mediumint(8) unsigned DEFAULT NULL,
  `director` varchar(300) NOT NULL DEFAULT '',
  `country` varchar(300) NOT NULL DEFAULT '',
  `actor` varchar(300) NOT NULL DEFAULT '',
  `description1` mediumtext,
  `description2` mediumtext,
  `mainPicture` char(50) DEFAULT NULL,
  `translate` char(50) DEFAULT NULL,
  `picture` varchar(300) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.catalog_1: ~3 rows (приблизительно)
/*!40000 ALTER TABLE `catalog_1` DISABLE KEYS */;
INSERT INTO `catalog_1` (`id`, `alias`, `title`, `metaTitle`, `metaKeyword`, `metaDescription`, `genry`, `year`, `director`, `country`, `actor`, `description1`, `description2`, `mainPicture`, `translate`, `picture`) VALUES
	(4, 'colleague', 'Коллеги', '', '', '', 'драмма', 1962, 'Алексей Сахаров', 'СССР', 'Олег Анофриев, Эдуард Бредун, Владимир Кашпур, Василий Лановой, Василий Ливанов, Иван Любезнов, Владимир Марута, Евгения Мельникова, Ростислав Плятт, Лев Поляков', '<p>\r\n	Советская др</p>\r\n', '<p>\r\n	Саша, Владька и Алеша — друзья со времен учебы в Ленинградском медицинском институте. После окончания учебного заведения все трое получают распределения на работу. Саша решает отправится в сельскую больницу, где два года не было врачей. Алексей трудится в карантинной службе международного врача. Несмотря на то, что судьба разбросала друзей по разным городам, нить их дружбы не прервется, а станет только крепче. Драма «Коллеги» основана на одноименной повести Василия Аксенова. Из-за обвинения писателя в диссидентстве, фильм долгое время пролежал на полке, запрещенный цензурой.</p>\r\n', '1.4-fld8.jpg', 'одноголосый любительский', '1.4-fld10-1.jpg|1.4-fld10-2.gif|1.4-fld10-3.jpg|1.4-fld10-4.gif|1.4-fld10-5.jpg|1.4-fld10-6.jpg|1.4-fld10-7.jpg|1.4-fld10-8.jpg|1.4-fld10-9.jpg|1.4-fld10-10.jpg'),
	(5, 'vaselisa-prekrasnaya', 'Василиса Прекрасная', '', '', '', 'для детей, сказка', 1939, 'Александр Роу', 'СССР', 'Ирина Зарубина, Сергей Столяров, Георгий Милляр', '<p>\r\n	Шедевр Алекс</p>\r\n', '<p>\r\n	Один отец задумал женить своих сыновей. Три брата вышли в чисто поле, натянули тетиву луков и пустили стрелы по разные стороны. Стрела старшего сына упала на двор к боярину, и стала боярская дочь его женой. Средний сын запустил стрелу на купеческий двор, где он тоже нашел себе невесту. А стрела младшего сына Ивана упала на болото, прямо в лапы к лягушке-квакушке. Не знал Иван, что лягушка оказалась непростой, а заколдованной красавицей Василисой Прекрасной. Стали они жить одной семьей. Но невесты брата позавидовали красоте Василисы и сожгли лягушачью шкурку. Змей Горыныч унес девушку к себе, и безутешный Иван отправился на поиски суженой.</p>\r\n', '1.5-fld8.jpg', '', ''),
	(7, 'podkidiysh', 'Подкидыш', '', '', '', 'комедия, драмма', 1939, 'Татьяна Лукашевич', 'СССР', 'Вероника Лебедева, Фаина Раневская, Петр Репнин, Ростислав Плятт, Рина Зеленая, Ольга Жизнева, Татьяна Барышева, Дмитрий Глухов, Федор Одиноков, Николай', '<p>\r\n	Классическая</p>\r\n', '<p>\r\n	Классическая комедия советского кинематографа. Маленькая Наташа вышла из дома и потерялась в большом городе. В ее судьбе приняли участие все, кого она встретила в своем увлекательном, полном веселых приключений путешествии. Все, конечно, закончилось хорошо. А пока Наташа блуждала по городу, она приобрела много друзей и среди взрослых, и среди детей. «Подкидыш» — одна из самых известных комедий отечественного кино советского периода, а фраза «Муля, не нервируй меня» превратилась в визитную карточку Фаины Раневской.</p>\r\n', '1.7-fld8.jpg', '', '');
/*!40000 ALTER TABLE `catalog_1` ENABLE KEYS */;

-- Дамп структуры для таблица cms.chatBan
DROP TABLE IF EXISTS `chatBan`;
CREATE TABLE IF NOT EXISTS `chatBan` (
  `ip` char(26) NOT NULL,
  `date` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.chatBan: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `chatBan` DISABLE KEYS */;
/*!40000 ALTER TABLE `chatBan` ENABLE KEYS */;

-- Дамп структуры для таблица cms.comment
DROP TABLE IF EXISTS `comment`;
CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupId` smallint(5) unsigned NOT NULL,
  `userId` int(10) unsigned DEFAULT NULL,
  `date` int(10) unsigned NOT NULL,
  `name` char(25) NOT NULL,
  `text` varchar(500) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupId` (`groupId`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.comment: 4 rows
/*!40000 ALTER TABLE `comment` DISABLE KEYS */;
INSERT INTO `comment` (`id`, `groupId`, `userId`, `date`, `name`, `text`, `status`, `ip`) VALUES
	(36, 14, 1, 1391801260, 'Администратор', '12345', 1, '127.0.0.1'),
	(34, 13, 1, 1391801237, 'Администратор', 'Вот это да...', 1, '127.0.0.1'),
	(35, 13, 1, 1391801244, 'Пётр', 'Это тестовый комментарий.', 1, '127.0.0.1'),
	(37, 15, 1, 1391801275, 'Администратор', '54321', 1, '127.0.0.1');
/*!40000 ALTER TABLE `comment` ENABLE KEYS */;

-- Дамп структуры для таблица cms.commentGroup
DROP TABLE IF EXISTS `commentGroup`;
CREATE TABLE IF NOT EXISTS `commentGroup` (
  `link` char(40) NOT NULL,
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  UNIQUE KEY `link` (`link`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.commentGroup: 3 rows
/*!40000 ALTER TABLE `commentGroup` DISABLE KEYS */;
INSERT INTO `commentGroup` (`link`, `id`) VALUES
	('article/view/about', 13),
	('article/blog/news/130411', 14),
	('article/blog/news/13-410', 15);
/*!40000 ALTER TABLE `commentGroup` ENABLE KEYS */;

-- Дамп структуры для таблица cms.demotivator
DROP TABLE IF EXISTS `demotivator`;
CREATE TABLE IF NOT EXISTS `demotivator` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(40) NOT NULL,
  `image` char(15) NOT NULL,
  `author` char(30) NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL,
  `metaKeyword` varchar(300) DEFAULT NULL,
  `metaDescription` varchar(300) DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.demotivator: 4 rows
/*!40000 ALTER TABLE `demotivator` DISABLE KEYS */;
INSERT INTO `demotivator` (`id`, `title`, `image`, `author`, `date`, `metaKeyword`, `metaDescription`, `status`) VALUES
	(2, 'Алкоголь утончает восприятие', '1365782151.jpg', 'root', 1365782151, NULL, NULL, 1),
	(3, 'Скоро пятница - я бы сдул...', '1365782262.jpg', 'root', 1365782262, NULL, NULL, 1),
	(4, 'Если в слове хеб сделать 4 ошибки...', '1365782385.jpg', 'root', 1365782385, NULL, NULL, 1),
	(11, '', '1390578841.jpg', '', 1390578841, NULL, NULL, 0);
/*!40000 ALTER TABLE `demotivator` ENABLE KEYS */;

-- Дамп структуры для таблица cms.faq
DROP TABLE IF EXISTS `faq`;
CREATE TABLE IF NOT EXISTS `faq` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(25) NOT NULL,
  `question` varchar(400) NOT NULL,
  `answer` text,
  `email` char(30) DEFAULT NULL,
  `date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.faq: 4 rows
/*!40000 ALTER TABLE `faq` DISABLE KEYS */;
INSERT INTO `faq` (`id`, `name`, `question`, `answer`, `email`, `date`) VALUES
	(1, 'Константин', 'Я хочу добавить в меню ссылку на уже существующую статью, но система всегда предлагает мне создать новую статью.', 'Для добавления ссылки на уже существующую статью, или любую другую страницу, просто выберите в блоке "тип создаваемой страницы:" значение "произвольная ссылка", затем в поле "ссылка" впишите адрес уже созданной страницы. Ссылку нужно указывать без "http" и имени домена, то есть примерно так: "article/view/about"', 'user1@example.com', 1365710400),
	(2, 'Муххамед', 'Поддерживается ли кеширование?', 'Да, часть информации кешируется. Кешируются шаблоны сайта, виджеты, а также некоторая дополнительная информация. Управление кешированием - это задача разработчика, поэтому для конечного пользователя оно происходит незаметно. В случае необходимости вы можете временно отключить кеширование, для этого в "общих настройках" нужно включить режим отладки. Файлы кеша записываются в директорий /cache вашего сайта.', 'user2@example.com', 1365710400),
	(3, 'Мария', 'Насколько хороша данная система для поисковых систем?', 'Для целей SEO есть несколько весомых аргументов:\r\n - возможность практически без ограничений модифицировать вид ссылок на страницы;\r\n - мета-теги для всех значимых страниц сайта (не значимые - это, например, страница авторизации или восстановления пароля);\r\n - автоматическая генерация карты сайта (sitemap.xml);\r\n - автоматическая поддержка заголовка "Last modified";\r\n - высокая скорость загрузки страниц сайта;\r\n - поддрежка .pda-версии;\r\n - поддержка микроразметки.', 'user3@example.com', 1365710400),
	(9, 'Андрей', 'Как поменять дизайн?', NULL, 'andrey@example.com', 1380465363);
/*!40000 ALTER TABLE `faq` ENABLE KEYS */;

-- Дамп структуры для таблица cms.forumCategory
DROP TABLE IF EXISTS `forumCategory`;
CREATE TABLE IF NOT EXISTS `forumCategory` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(200) NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `metaTitle` varchar(500) DEFAULT NULL,
  `metaKeyword` varchar(500) DEFAULT NULL,
  `metaDescription` varchar(800) DEFAULT NULL,
  `newTopic` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `newPost` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.forumCategory: ~2 rows (приблизительно)
/*!40000 ALTER TABLE `forumCategory` DISABLE KEYS */;
INSERT INTO `forumCategory` (`id`, `title`, `sort`, `metaTitle`, `metaKeyword`, `metaDescription`, `newTopic`, `newPost`) VALUES
	(1, 'Category 1', 1, '', '', '', 1, 1),
	(3, 'Category 2', 2, '', '', '', 1, 1);
/*!40000 ALTER TABLE `forumCategory` ENABLE KEYS */;

-- Дамп структуры для таблица cms.forumPost
DROP TABLE IF EXISTS `forumPost`;
CREATE TABLE IF NOT EXISTS `forumPost` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topicId` mediumint(8) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.forumPost: ~8 rows (приблизительно)
/*!40000 ALTER TABLE `forumPost` DISABLE KEYS */;
INSERT INTO `forumPost` (`id`, `topicId`, `userId`, `date`, `message`) VALUES
	(23, 8, 1, 1408986613, 'Message 1.'),
	(24, 8, 1, 1408987275, 'Message 2.'),
	(25, 8, 1, 1408987283, 'Message 3.'),
	(26, 8, 1, 1408987292, 'Message 4.'),
	(27, 8, 1, 1409173115, 'Строка 1 строка.\r\nВтроая\r\nи треться <font style="color:red;">строка</a>.'),
	(28, 8, 1, 1409173878, 'Пример текста <a href="http://ya.ru">со ссылкой</a> и <b>жирным текстом</b>.'),
	(29, 8, 1, 1409174068, '111222333'),
	(30, 8, 1, 1409174304, 'Тут есть всё: [b]жирный текст[/B], другой: [I]италик[/i], ещё [u]подчёркнутый[/U].\r\nВот картинка: [img]http://yabs.yandex.ru/count/CrIJezfdNre40002gP0088wph-G_1L6L0fi6QLg8itm32mUcXGcAj_VSIG6g0gMM66IGe4oRiP6yq4ba1fClGQxyAt43BlEY0GMn0Rlen37C2vRs5BxJV0CBHm1WUGe0[/img].');
/*!40000 ALTER TABLE `forumPost` ENABLE KEYS */;

-- Дамп структуры для таблица cms.forumTopic
DROP TABLE IF EXISTS `forumTopic`;
CREATE TABLE IF NOT EXISTS `forumTopic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` smallint(5) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `title` char(200) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `lastDate` int(10) unsigned NOT NULL DEFAULT '0',
  `postCount` smallint(5) unsigned NOT NULL DEFAULT '0',
  `message` text,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.forumTopic: ~3 rows (приблизительно)
/*!40000 ALTER TABLE `forumTopic` DISABLE KEYS */;
INSERT INTO `forumTopic` (`id`, `categoryId`, `userId`, `title`, `date`, `lastDate`, `postCount`, `message`, `status`) VALUES
	(8, 1, 1, 'Topic 1', 1408985261, 1409174304, 8, 'p oijp oqewiv jeoriuvhui hvoiu hoiuh ', 1),
	(9, 1, 1, 'Topic 2', 1408988452, 0, 0, 'p aoivja pvoiv jpo ij', 1),
	(10, 1, 1, 'Topic 3', 1408988470, 0, 0, 'pq fioje vpiwuevh oeriu houih', 1);
/*!40000 ALTER TABLE `forumTopic` ENABLE KEYS */;

-- Дамп структуры для таблица cms.forumUser
DROP TABLE IF EXISTS `forumUser`;
CREATE TABLE IF NOT EXISTS `forumUser` (
  `id` int(10) unsigned NOT NULL,
  `login` char(25) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `ip` char(15) DEFAULT NULL,
  `avatar` char(11) DEFAULT NULL,
  `postCount` smallint(5) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Дамп данных таблицы cms.forumUser: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `forumUser` DISABLE KEYS */;
INSERT INTO `forumUser` (`id`, `login`, `date`, `ip`, `avatar`, `postCount`, `status`) VALUES
	(1, 'root', 1408729824, '127.0.0.1', '5.jpeg', 11, 0);
/*!40000 ALTER TABLE `forumUser` ENABLE KEYS */;

-- Дамп структуры для таблица cms.frmField
DROP TABLE IF EXISTS `frmField`;
CREATE TABLE IF NOT EXISTS `frmField` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `formId` mediumint(8) unsigned NOT NULL,
  `title_ru` char(25) NOT NULL,
  `htmlType` enum('text','radio','select','checkbox','textarea','email','file','captcha') NOT NULL,
  `data_ru` text,
  `defaultValue` varchar(100) NOT NULL DEFAULT '0',
  `required` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sort` tinyint(3) NOT NULL DEFAULT '0',
  `title_en` char(25) NOT NULL,
  `data_en` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=80 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.frmField: 14 rows
/*!40000 ALTER TABLE `frmField` DISABLE KEYS */;
INSERT INTO `frmField` (`id`, `formId`, `title_ru`, `htmlType`, `data_ru`, `defaultValue`, `required`, `sort`, `title_en`, `data_en`) VALUES
	(6, 1000, 'Ваше имя', 'text', '', '', 1, 1, 'Your name', ''),
	(7, 1000, 'E-mail или телефон', 'text', '', '', 0, 2, 'E-mail or phone', ''),
	(8, 1000, 'Сообщение', 'textarea', '', '', 1, 3, 'Message', ''),
	(9, 1001, 'Ваше имя', 'text', '', '', 1, 1, 'Ваше имя', ''),
	(10, 1001, 'Телефон', 'text', '', '', 1, 2, 'Телефон', ''),
	(11, 1001, 'Желаемое время звонка', 'text', '', '', 1, 3, 'Желаемое время звонка', ''),
	(12, 1002, 'Ваше имя', 'text', '', '', 1, 1, 'Ваше имя', ''),
	(13, 1002, 'Телефон', 'text', '', '', 1, 2, 'Телефон', ''),
	(14, 1002, 'Удобное время звонка', 'text', '', '', 0, 3, 'Удобное время звонка', ''),
	(24, 1000, 'Откуда вы узнали о нас?', 'select', 'поисковик|от друзей|сайт example.com|другое', '', 1, 4, 'How did you hear about us', 'search engine|form friends|site example.com|other'),
	(25, 1000, 'Введите текст с картинки', 'captcha', '', '', 1, 5, 'Provide the text on the p', ''),
	(30, 999, 'Телефон', 'text', '', '', 0, 2, 'Телефон', ''),
	(29, 999, 'Ваше имя', 'text', '', '', 1, 1, 'Ваше имя', ''),
	(31, 999, 'E-mail', 'email', '', 'cfg', 0, 3, 'E-mail', '');
/*!40000 ALTER TABLE `frmField` ENABLE KEYS */;

-- Дамп структуры для таблица cms.frmForm
DROP TABLE IF EXISTS `frmForm`;
CREATE TABLE IF NOT EXISTS `frmForm` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title_ru` char(35) NOT NULL,
  `email` varchar(30) NOT NULL,
  `subject_ru` varchar(150) NOT NULL,
  `successMessage_ru` text NOT NULL,
  `redirect` varchar(255) DEFAULT NULL,
  `formView` varchar(15) DEFAULT NULL,
  `script` varchar(15) DEFAULT NULL,
  `title_en` char(35) NOT NULL,
  `subject_en` varchar(150) NOT NULL,
  `successMessage_en` text NOT NULL,
  `notification` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1021 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.frmForm: 4 rows
/*!40000 ALTER TABLE `frmForm` DISABLE KEYS */;
INSERT INTO `frmForm` (`id`, `title_ru`, `email`, `subject_ru`, `successMessage_ru`, `redirect`, `formView`, `script`, `title_en`, `subject_en`, `successMessage_en`, `notification`) VALUES
	(1000, 'Контакты', 'cfg', 'cms0: сообщение с сайта', '<p>Сообщение получено, большое спасибо.</p>', '', '', '', 'Contacts', 'cms0: message from the site', '<p>\r\n	The message was got. Thank you.</p>\r\n', NULL),
	(1001, 'Обратная связь', 'cfg', 'cms0: обратный звонок', '<p>\r\n	Спасибо, мы обязательно позвоним вам!</p>\r\n', '', '', '', 'Обратная связь', 'cms0: обратный звонок', '<p>\r\n	Спасибо, мы обязательно позвоним вам!</p>\r\n', NULL),
	(1002, 'Callback', 'cfg', 'Callback on CMS site', '<p>\r\n	Your request has been commited. We will contact to you.</p>\r\n', '', '', '', 'Обратный звонок', 'Обратный звонок на сайте CMS', '<p>\r\n	Заявка на обратный звонок принята. Мы обязательно свяжемся с вами.</p>\r\n', NULL),
	(999, 'Оформление заказа', 'cfg', 'Заказ с сайта', '', NULL, NULL, 'shop', 'Оформление заказа', 'Заказ с сайта', '', NULL);
/*!40000 ALTER TABLE `frmForm` ENABLE KEYS */;

-- Дамп структуры для таблица cms.menu
DROP TABLE IF EXISTS `menu`;
CREATE TABLE IF NOT EXISTS `menu` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.menu: 2 rows
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` (`id`, `title`) VALUES
	(1, 'Верхнее'),
	(5, 'Для покупателей');
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;

-- Дамп структуры для таблица cms.menuItem
DROP TABLE IF EXISTS `menuItem`;
CREATE TABLE IF NOT EXISTS `menuItem` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `menuId` smallint(5) unsigned NOT NULL,
  `typeId` smallint(5) unsigned NOT NULL,
  `link` varchar(255) NOT NULL,
  `title_ru` char(30) NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `title_en` char(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `menuId` (`menuId`)
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.menuItem: 14 rows
/*!40000 ALTER TABLE `menuItem` DISABLE KEYS */;
INSERT INTO `menuItem` (`id`, `parentId`, `menuId`, `typeId`, `link`, `title_ru`, `sort`, `title_en`) VALUES
	(1, 0, 1, 1, 'article/view/index', 'Главная', 1, 'Main'),
	(2, 0, 1, 1, 'article/view/about', 'О нас', 2, 'About us'),
	(3, 0, 1, 2, 'article/blog/news', 'Новости', 4, 'News'),
	(4, 0, 1, 4, 'article/list/article', 'Статьи', 3, 'Articles'),
	(6, 0, 1, 11, 'catalog/1', 'Каталог', 5, 'Catalog'),
	(7, 0, 1, 6, 'faq', 'ЧаВо', 7, 'F.A.Q.'),
	(8, 0, 1, 5, 'shop', 'Магазин', 6, 'Shop'),
	(9, 29, 1, 9, 'demotivator', 'Галерея', 2, 'Gallery'),
	(10, 0, 1, 7, 'form/1000', 'Контакты', 10, 'Contacts'),
	(29, 0, 1, 3, '#', 'Демотиваторы', 8, 'Demotivators'),
	(30, 29, 1, 8, 'demotivator/construct', 'Конструктор', 1, 'Constructor'),
	(33, 0, 1, 13, 'forum', 'Форум', 9, 'Forum'),
	(34, 33, 1, 12, 'forum/profile', 'Профайл', 1, 'Profile'),
	(49, 33, 1, 10, 'chat', 'Чат', 2, 'Чат');
/*!40000 ALTER TABLE `menuItem` ENABLE KEYS */;

-- Дамп структуры для таблица cms.menuType
DROP TABLE IF EXISTS `menuType`;
CREATE TABLE IF NOT EXISTS `menuType` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(25) NOT NULL,
  `controller` char(20) NOT NULL,
  `action` char(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=520 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.menuType: 13 rows
/*!40000 ALTER TABLE `menuType` DISABLE KEYS */;
INSERT INTO `menuType` (`id`, `title`, `controller`, `action`) VALUES
	(1, 'Простая статья', 'article', 'menuArticle'),
	(2, 'Блог статей в категории', 'article', 'menuBlog'),
	(3, 'Произвольная ссылка', 'link', 'menuLink'),
	(4, 'Список статей в категории', 'article', 'menuList'),
	(5, 'Магазин: категории', 'shopSetting', 'menuCategory'),
	(6, 'Часто задаваемые вопросы', 'faq', 'menuList'),
	(7, 'Контактная форма', 'form', 'menuForm'),
	(8, 'Демотиватор: конструктор', 'demotivator', 'menuConstructor'),
	(9, 'Демотиватор: галерея', 'demotivator', 'menuGallery'),
	(11, 'Универсальный каталог', 'catalog', 'menuCatalog'),
	(12, 'Форум: личный кабинет', 'forum', 'menuProfile'),
	(13, 'Форум: разделы форума', 'forum', 'menuCategory'),
	(10, 'Чат', 'chat', 'menu');
/*!40000 ALTER TABLE `menuType` ENABLE KEYS */;

-- Дамп структуры для таблица cms.modified
DROP TABLE IF EXISTS `modified`;
CREATE TABLE IF NOT EXISTS `modified` (
  `link` char(120) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`link`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.modified: 43 rows
/*!40000 ALTER TABLE `modified` DISABLE KEYS */;
INSERT INTO `modified` (`link`, `time`) VALUES
	('article/view/222', 1461069025),
	('en/shop/category/37/flo-500', 1461065330),
	('shop/category/37/flo-500', 1461065284),
	('catalog/1/colleague', 1461068964),
	('catalog/1/vaselisa-prekrasnaya', 1461065224),
	('catalog/1/podkidiysh', 1461065216),
	('en/catalog/1/podkidiysh', 1461065172),
	('en/catalog/1/vaselisa-prekrasnaya', 1461065162),
	('en/catalog/1/colleague', 1461065153),
	('en/article/view/130412', 1461065137),
	('en/article/view/13-410', 1461065135),
	('en/article/view/130411', 1461065131),
	('en/article/list/news', 1494930386),
	('en/article/blog/news', 1494930386),
	('article/view/130412', 1461065114),
	('article/view/13-410', 1461065112),
	('article/view/130411', 1461065086),
	('article/list/news', 1461064839),
	('article/blog/news', 1461064839),
	('article/view/article-3', 1461064834),
	('article/view/article-1', 1461064765),
	('article/view/article-4', 1461064759),
	('article/list/article', 1461064755),
	('article/blog/article', 1461064755),
	('en/article/view/article-2', 1461064748),
	('en/article/view/article-3', 1461064740),
	('en/article/view/article-1', 1461064734),
	('en/article/view/article-4', 1461064728),
	('en/article/list/article', 1461064721),
	('en/article/blog/article', 1461064721),
	('en/article/view/index', 1461064716),
	('article/view/index', 1528301329),
	('article/view/about', 1462789962),
	('en/article/view/about', 1462790023),
	('article/blog/test', 1462824013),
	('article/list/test', 1462824013),
	('article/blog/222', 1462785952),
	('article/list/222', 1462785952),
	('en/article/blog/test', 1462794158),
	('en/article/list/test', 1462794158),
	('article/blog/test2', 1462807026),
	('article/list/test2', 1462807026),
	('article/view/170118', 1484776901);
/*!40000 ALTER TABLE `modified` ENABLE KEYS */;

-- Дамп структуры для таблица cms.oauth
DROP TABLE IF EXISTS `oauth`;
CREATE TABLE IF NOT EXISTS `oauth` (
  `id` bigint(15) unsigned NOT NULL,
  `social` enum('vk','facebook') NOT NULL,
  `userId` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.oauth: 0 rows
/*!40000 ALTER TABLE `oauth` DISABLE KEYS */;
/*!40000 ALTER TABLE `oauth` ENABLE KEYS */;

-- Дамп структуры для таблица cms.payment
DROP TABLE IF EXISTS `payment`;
CREATE TABLE IF NOT EXISTS `payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned DEFAULT NULL,
  `method` char(25) DEFAULT NULL,
  `status` enum('request','success','wrong','cancel') NOT NULL DEFAULT 'request',
  `date` int(10) unsigned NOT NULL,
  `amount` float unsigned NOT NULL DEFAULT '0',
  `data` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.payment: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment` ENABLE KEYS */;

-- Дамп структуры для таблица cms.section
DROP TABLE IF EXISTS `section`;
CREATE TABLE IF NOT EXISTS `section` (
  `name` char(20) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `widgetId` smallint(5) unsigned NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.section: 33 rows
/*!40000 ALTER TABLE `section` DISABLE KEYS */;
INSERT INTO `section` (`name`, `url`, `widgetId`, `sort`) VALUES
	('bottom', 'article/blog/news*', 9, 1),
	('bottom', 'article/list/article*', 9, 1),
	('right', 'article/view/index.', 10, 2),
	('right', 'article/view/index.', 11, 3),
	('footer', 'catalog/1/', 51, 1),
	('right', 'article/view/about.', 13, 4),
	('right', 'article/list/article/', 13, 4),
	('right', 'article/view/about.', 17, 5),
	('right', 'form/1000/', 17, 5),
	('bottom', 'article/view/index.', 53, 2),
	('right', 'faq/', 54, 6),
	('right', 'form/1000.', 54, 6),
	('footer', 'faq/', 51, 1),
	('footer', '#/', 51, 1),
	('footer', 'article/view/index/', 51, 1),
	('footer', 'form/1000/', 51, 1),
	('bottom', 'article/view/about.', 9, 1),
	('footer', 'article/blog/news/', 51, 1),
	('footer', 'article/list/article/', 51, 1),
	('footer', 'article/view/about/', 51, 1),
	('right', 'article/view/about.', 58, 7),
	('right', 'article/view/index.', 58, 7),
	('top', 'user/register.', 63, 1),
	('top', 'user/login.', 63, 1),
	('right', 'article/list/article/', 66, 8),
	('top', 'catalog/1/', 68, 2),
	('right', 'shop/', 15, 9),
	('right', 'article/view/index.', 14, 1),
	('right', 'shop/', 14, 1),
	('bottom', 'article/view/index.', 6, 3),
	('bottom', 'form/1000.', 77, 4),
	('bottom', 'article/view/index.', 78, 5),
	('bottom', 'article/list/article*', 79, 6);
/*!40000 ALTER TABLE `section` ENABLE KEYS */;

-- Дамп структуры для таблица cms.shpBrand
DROP TABLE IF EXISTS `shpBrand`;
CREATE TABLE IF NOT EXISTS `shpBrand` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(50) NOT NULL,
  `image` char(11) NOT NULL DEFAULT '',
  `text1` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.shpBrand: ~5 rows (приблизительно)
/*!40000 ALTER TABLE `shpBrand` DISABLE KEYS */;
INSERT INTO `shpBrand` (`id`, `title`, `image`, `text1`) VALUES
	(2, 'FLO', '2.jpeg', ''),
	(3, 'HUTER', '3.png', ''),
	(4, 'GERMAFLEX', '4.jpeg', ''),
	(5, 'ABAC', '5.jpeg', ''),
	(6, 'Fubag', '6.gif', '');
/*!40000 ALTER TABLE `shpBrand` ENABLE KEYS */;

-- Дамп структуры для таблица cms.shpCategory
DROP TABLE IF EXISTS `shpCategory`;
CREATE TABLE IF NOT EXISTS `shpCategory` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` smallint(5) unsigned NOT NULL DEFAULT '0',
  `alias` char(50) NOT NULL,
  `title` char(50) NOT NULL,
  `text1` mediumtext,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `image` char(11) DEFAULT NULL,
  `feature` varchar(1000) NOT NULL DEFAULT '',
  `metaTitle` varchar(500) NOT NULL DEFAULT '',
  `metaKeyword` varchar(500) NOT NULL DEFAULT '',
  `metaDescription` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.shpCategory: ~5 rows (приблизительно)
/*!40000 ALTER TABLE `shpCategory` DISABLE KEYS */;
INSERT INTO `shpCategory` (`id`, `parentId`, `alias`, `title`, `text1`, `sort`, `image`, `feature`, `metaTitle`, `metaKeyword`, `metaDescription`) VALUES
	(35, 0, 'tools', 'Инстументы', '', 0, '35.jpg', '12,13,14,16,17,18,21,21', '', '', ''),
	(36, 0, 'equipment', 'Оборудование', '', 0, '36.png', '16,17,18,21', '', '', ''),
	(37, 35, 'chainsaw', 'Бензопилы', '', 0, '37.jpg', '12,13,14,21', '', '', ''),
	(38, 35, 'screwdrivers', 'Шуруповёрты', '', 0, '38.jpg', '16,20', '', '', ''),
	(39, 36, 'compressors', 'Компрессоры', '', 0, '39.jpg', '18,17,21', '', '', '');
/*!40000 ALTER TABLE `shpCategory` ENABLE KEYS */;

-- Дамп структуры для таблица cms.shpFeature
DROP TABLE IF EXISTS `shpFeature`;
CREATE TABLE IF NOT EXISTS `shpFeature` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('text','checkbox','select') NOT NULL,
  `groupId` smallint(5) unsigned NOT NULL,
  `title` char(100) NOT NULL,
  `unit` char(12) NOT NULL DEFAULT '',
  `variant` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `data` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.shpFeature: ~8 rows (приблизительно)
/*!40000 ALTER TABLE `shpFeature` DISABLE KEYS */;
INSERT INTO `shpFeature` (`id`, `type`, `groupId`, `title`, `unit`, `variant`, `data`) VALUES
	(12, 'text', 4, 'Длина шины', 'мм', 1, ''),
	(13, 'text', 4, 'Шаг цепи', 'мм', 0, ''),
	(14, 'text', 4, 'Толщина ведущего звена', 'мм', 0, ''),
	(16, 'text', 5, 'Скорость', 'об/мин', 0, ''),
	(17, 'text', 6, 'Объём бака', 'л', 0, ''),
	(18, 'text', 6, 'Максимальное давление', 'бар', 0, ''),
	(20, 'text', 7, 'Вес', 'кг', 0, ''),
	(21, 'text', 7, 'Мощность', 'Вт', 0, '');
/*!40000 ALTER TABLE `shpFeature` ENABLE KEYS */;

-- Дамп структуры для таблица cms.shpFeatureGroup
DROP TABLE IF EXISTS `shpFeatureGroup`;
CREATE TABLE IF NOT EXISTS `shpFeatureGroup` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.shpFeatureGroup: ~4 rows (приблизительно)
/*!40000 ALTER TABLE `shpFeatureGroup` DISABLE KEYS */;
INSERT INTO `shpFeatureGroup` (`id`, `title`) VALUES
	(4, 'Бензопилы'),
	(5, 'Электроинструмент'),
	(6, 'Компрессоры'),
	(7, 'Общие');
/*!40000 ALTER TABLE `shpFeatureGroup` ENABLE KEYS */;

-- Дамп структуры для таблица cms.shpProduct
DROP TABLE IF EXISTS `shpProduct`;
CREATE TABLE IF NOT EXISTS `shpProduct` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` smallint(5) unsigned NOT NULL,
  `brandId` mediumint(8) unsigned DEFAULT NULL,
  `alias` char(25) NOT NULL,
  `title` char(60) NOT NULL,
  `text1` text,
  `text2` mediumtext,
  `price` float unsigned NOT NULL DEFAULT '0',
  `mainImage` char(13) NOT NULL DEFAULT '',
  `image` varchar(255) DEFAULT NULL,
  `metaTitle` varchar(300) DEFAULT '',
  `metaKeyword` varchar(300) DEFAULT '',
  `metaDescription` varchar(300) DEFAULT '',
  `variant` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `quantity` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.shpProduct: ~8 rows (приблизительно)
/*!40000 ALTER TABLE `shpProduct` DISABLE KEYS */;
INSERT INTO `shpProduct` (`id`, `categoryId`, `brandId`, `alias`, `title`, `text1`, `text2`, `price`, `mainImage`, `image`, `metaTitle`, `metaKeyword`, `metaDescription`, `variant`, `quantity`) VALUES
	(58, 37, 4, 'germaflex-yd-kw02-45', 'Бензопила GERMAFLEX YD-KW02-45', '<p>\r\n	Пила цепная бензиновая (бензопила) GermaFlex</p>\r\n', '<p>\r\n	Пила цепная бензиновая (бензопила) GermaFlex - это мощный и высокотехнологичный бензоинструмент, снабженный карбюратором производства фирмы Walbro, который отличается низким уровнем токсичности выхлопных газов. Данная модель работает на специальной смеси, состоящей из масла для двухтактных двигателей и бензина марки АИ 92. Хромированный цилиндр мотора и кованый коленвал обеспечивают значительный моторесурс. Цепная бензопила GermaFlex оснащена декомпрессионным клапаном, рукояткой эргономичной формы для надежного удержания инструмента и системой автоматической смазки цепи, которая заправляется специальным адгезионным маслом. Благодаря антивибрационной системе, значительно уменьшена нагрузка на оператора. Мотопила укомплектована шиной и цепью GermaFlex.</p>\r\n', 3298, '58.1.jpeg', '58.1.jpeg', '', '', '', 0, 0),
	(59, 37, 3, 'huter-bs-45', 'Бензопила HUTER BS-45', '', '<p>\r\n	Бензопила Huter BS-45 оснащена двигателем мощностью 2.3 л.с. Это инструмент для бытового использования - распилки дров для камина, ухода за садом, небольшого строительства. Бензопила оборудована тормозом цепи для обеспечения безопасной работы. В комплект поставки входит 45 сантиметровая шина. Есть антивибрационная система.</p>\r\n', 4318, '59.1.jpg', '59.1.jpg,59.2.jpg,59.3.jpg,59.4.jpg', '', '', '', 1, 0),
	(60, 37, 2, 'flo-500', 'Бензопила FLO 500', '<p>\r\n	Бензин АИ92, ручной пуск.</p>\r\n', '<p>\r\n	Безопила предназначена для работы в домохозяйствах. Бензопила предназначена исключительно для пиления по дереву. Поскольку в качестве привода пилы используется двигатель внутреннего сгорания, допускается пиление исключительно в условиях открытого пространства или подготовленного надлежащим образом помещения.</p>\r\n<p>\r\n	В комплекте с бензопилой поставляется:<br />\r\n	- напрявляющая цепи;<br />\r\n	- режущая цепь;<br />\r\n	- колпак от напрвляющей</p>\r\n', 4590, '60.1.jpg', '60.1.jpg,60.2.jpg,60.3.jpg', '', '', '', 0, 0),
	(61, 38, NULL, 'bass-bp-5310', 'BASS BP-5310', '', '<p>\r\n Шуруповерт подходит для работы профессионалов в любых условиях, подходит для повышенных нагрузок. Изготовлен из высококачественного пластика, что обеспечивает большую прочность оборудования. Удобный и надежный дизайн. Шуруповерт имеет 20 режимов, что делает его идеальным для заворачивания шурупов. Можно также использовать для сверления.</p>\r\n', 4114, '61.1.jpg', '61.1.jpg,61.2.jpg,61.3.jpg', '', '', '', 0, 0),
	(62, 39, 6, 'fubag-auto-master-kit', 'Компрессор Fubag Auto Master Kit', '<p>\r\n	Набор компрессорного оборудования полностью готов к работе и состоит из масляного компрессора, набора пневмоинструмента и аксессуаров к нему.</p>\r\n', '<p>\r\n	Набор компрессорного оборудования полностью готов к работе и состоит из масляного компрессора, набора пневмоинструмента и аксессуаров к нему. Набор предназначен в первую очередь для того, кто ценит своё рабочее время и предпочитает удобство во всём. Сочетание "два в одном" позволяет проводить большой спектр работ с применением сжатого воздуха без покупки дополнительных комплектующих.</p>\r\n<p>\r\n	Набор предназначен для автолюбителей и небольших автомастерских.</p>\r\n<p>\r\n	Набор компрессорного оборудования AUTO MASTER KIT В набор входит компактный передвижной масляный компрессор с ресивером на 50 л, обеспечивающий давление до 9 бар, ударный пневмогайковёрт с насадками, пистолета с манометром для накачки шин, краскораспылитель с верхним бачком на 0,5 л, пневмопистолет для вязких жидкостей, комплект из трёх наконечников, пневмопистолет для продувки или мойки, насадка для продувки, домкрат и гибкий спиральный резиновый шланг длиной 5 м с быстроразъёмными соединениями.</p>\r\n', 18645, '62.1.jpeg', '62.1.jpeg', '', '', '', 0, 0),
	(63, 39, 4, 'germaflex-500l-w20-8', 'Компрессор Germaflex 500L W-2.0/8 500L', '', '<p>\r\n	Компрессор "GERMAFLEX" применяется для покрасочных работ, накачки шин, продувки фильтров и выполнения других подобных операций с использованием пневмоинструмента. Аппарат оснащен пятисотлитровым ресивером, что существенно сокращает интенсивность рабочих циклов. Технические характеристики: мощность, кВт: 12 Производительность, л/мин: 2000 Объём ресивера, л.: 500 Рабочее давление, бар: 10 Вес, кг: 450</p>\r\n', 45999, '63.1.jpeg', '63.1.jpeg,63.2.jpeg', '', '', '', 0, 0),
	(64, 39, 5, 'genesis-11-08-500', 'Компрессор GENESIS 11 08/500', '<p>\r\n	Компрессор GENESIS представляет собой полностью готовую к эксплуатации компрессорную станцию, что достигается за счет наличия: осушителя, который позволяет получить сухой воздух; системы фильтрации, которая удаляет твердые частицы и примеси масла</p>\r\n', '<p>\r\n	Компрессор GENESIS представляет собой полностью готовую к эксплуатации компрессорную станцию, что достигается за счет наличия:</p>\r\n<ul>\r\n	<li>\r\n		осушителя, который позволяет получить сухой воздух;</li>\r\n	<li>\r\n		системы фильтрации, которая удаляет твердые частицы и примеси масла;</li>\r\n	<li>\r\n		ресивера, накапливающего сжатый воздух;</li>\r\n	<li>\r\n		микропроцессорного блока управления MC2 обеспечивающего управление и контроль всех компонентов компрессорной станции в автоматическом режиме реального времени и обеспечи. вающего режим энергосбережения;</li>\r\n	<li>\r\n		электрощита управления, дающего возможность безопасно осуществлять все электроподключения и эксплуатацию;</li>\r\n	<li>\r\n		виброизоляционного, звукопоглощающего корпуса, обеспечивающего самый быстрый и легкий доступ, в своем классе компрессоров, ко всем внутренним частях компрессора для подключения и технического обслуживания.</li>\r\n</ul>\r\n<p>\r\n	Все модели оснащены микропроцессорным блоком управления МС2 на русском языке, обеспечивающим управление и контроль всех компонентов компрессорной станции в автоматическом режиме реального времени с выводом на дисплей параметров его работы и позволяющим соединять в единую систему до 4 (10) компрессоров, что позволяет снизить энергозатраты и упростить эксплуатацию компрессорной станции.</p>\r\n', 317612, '64.1.jpg', '64.1.jpg', '', '', '', 0, 0),
	(65, 39, 5, 'genesis-i-22-4-10', 'Винтовой компрессор ABAC GENESIS.I 22 4', '<p>\r\n	10 Бар с блоком частотного регулирования</p>\r\n', '<p>\r\n	Винтовой компрессор ABAC GENESIS.I 22 4-10 Бар &nbsp;– это полноценная компрессорная станция, спроектированная по принципу Plug&amp;Play (Включи и работай). Винтовые пары компрессора разработаны специально для оборудования ABAC и идеально подогнаны. Самостоятельный электрощит для безопасного подключения и эксплуатации всех компонентов компрессорной станции</p>\r\n<p>\r\n	Блок частотного регулирования производительности и давления. Шумо- и вибропоглощающий корпус. Ременная передача с автоматическим регулятором натяжения ремня. Тройная фильтрация и осушитель воздуха позволяют подавать максимально очищенный сжатый воздух.</p>\r\n<p>\r\n	Компьютерный блок автоматического управления MC2 обеспечивает полный контроль работы всех компонентов станции. Все данные выводятся на русском языке. Возможность соединить в одну систему от 4 до 10 компрессоров. Режим энергосбережения.</p>\r\n', 850000, '65.1.jpg', '65.1.jpg', '', '', '', 0, 0);
/*!40000 ALTER TABLE `shpProduct` ENABLE KEYS */;

-- Дамп структуры для таблица cms.shpProductFeature
DROP TABLE IF EXISTS `shpProductFeature`;
CREATE TABLE IF NOT EXISTS `shpProductFeature` (
  `productId` int(10) unsigned NOT NULL,
  `featureId` smallint(5) unsigned NOT NULL,
  `value` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.shpProductFeature: ~23 rows (приблизительно)
/*!40000 ALTER TABLE `shpProductFeature` DISABLE KEYS */;
INSERT INTO `shpProductFeature` (`productId`, `featureId`, `value`) VALUES
	(61, 16, '0-900 об/мин'),
	(61, 20, '1.7 кг'),
	(59, 12, '450 мм'),
	(59, 13, '0.83 мм'),
	(59, 21, '1700 Вт'),
	(58, 12, '470 мм'),
	(58, 13, '3.25 мм'),
	(58, 14, '1.5 мм'),
	(58, 21, '1800 Вт'),
	(65, 17, '500 л'),
	(65, 18, '15 бар'),
	(65, 21, '22000 Вт'),
	(64, 17, '500 л'),
	(64, 18, '12 бар'),
	(64, 21, '11000 Вт'),
	(63, 17, '500 л'),
	(63, 18, '18 бар'),
	(63, 21, '12000 Вт'),
	(62, 17, '50 л'),
	(62, 18, '9 бар'),
	(62, 21, '15000 Вт'),
	(60, 12, '500 мм'),
	(60, 21, '2200 Вт');
/*!40000 ALTER TABLE `shpProductFeature` ENABLE KEYS */;

-- Дамп структуры для таблица cms.shpProductGroup
DROP TABLE IF EXISTS `shpProductGroup`;
CREATE TABLE IF NOT EXISTS `shpProductGroup` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.shpProductGroup: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `shpProductGroup` DISABLE KEYS */;
INSERT INTO `shpProductGroup` (`id`, `title`) VALUES
	(1, 'Избранные товары');
/*!40000 ALTER TABLE `shpProductGroup` ENABLE KEYS */;

-- Дамп структуры для таблица cms.shpProductGroupItem
DROP TABLE IF EXISTS `shpProductGroupItem`;
CREATE TABLE IF NOT EXISTS `shpProductGroupItem` (
  `groupId` smallint(5) unsigned NOT NULL,
  `productId` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.shpProductGroupItem: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `shpProductGroupItem` DISABLE KEYS */;
INSERT INTO `shpProductGroupItem` (`groupId`, `productId`) VALUES
	(1, 61);
/*!40000 ALTER TABLE `shpProductGroupItem` ENABLE KEYS */;

-- Дамп структуры для таблица cms.shpVariant
DROP TABLE IF EXISTS `shpVariant`;
CREATE TABLE IF NOT EXISTS `shpVariant` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `productId` int(10) unsigned NOT NULL,
  `title` char(60) NOT NULL,
  `feature` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.shpVariant: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `shpVariant` DISABLE KEYS */;
INSERT INTO `shpVariant` (`id`, `productId`, `title`, `feature`) VALUES
	(1, 59, 'HUTER BS-45M', 'a:1:{i:12;s:8:"400 мм";}');
/*!40000 ALTER TABLE `shpVariant` ENABLE KEYS */;

-- Дамп структуры для таблица cms.user
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupId` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `login` char(35) NOT NULL,
  `password` char(32) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `email` char(30) DEFAULT NULL,
  `code` varchar(32) DEFAULT NULL,
  `data` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.user: 3 rows
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`id`, `groupId`, `login`, `password`, `status`, `email`, `code`, `data`) VALUES
	(2, 250, 'admin', '2fzr2Ln.VAGRA', 1, 'admin@mail.com', '', NULL),
	(3, 200, 'moderator', '2fy8kc7l/6nd6', 1, 'editro@mail.com', '', NULL),
	(1, 255, 'root', '2fzr2Ln.VAGRA', 1, 'root@example.com', NULL, NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

-- Дамп структуры для таблица cms.userGroup
DROP TABLE IF EXISTS `userGroup`;
CREATE TABLE IF NOT EXISTS `userGroup` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=256 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.userGroup: 4 rows
/*!40000 ALTER TABLE `userGroup` DISABLE KEYS */;
INSERT INTO `userGroup` (`id`, `name`) VALUES
	(255, 'суперпользователь'),
	(250, 'администратор'),
	(200, 'модератор'),
	(1, 'зарегистрированный');
/*!40000 ALTER TABLE `userGroup` ENABLE KEYS */;

-- Дамп структуры для таблица cms.userMessage
DROP TABLE IF EXISTS `userMessage`;
CREATE TABLE IF NOT EXISTS `userMessage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user1Id` int(10) unsigned NOT NULL,
  `user1Login` char(25) NOT NULL,
  `user2Id` int(10) unsigned NOT NULL,
  `user2Login` char(25) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `isNew` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.userMessage: 14 rows
/*!40000 ALTER TABLE `userMessage` DISABLE KEYS */;
INSERT INTO `userMessage` (`id`, `user1Id`, `user1Login`, `user2Id`, `user2Login`, `date`, `isNew`, `message`) VALUES
	(5, 2, 'test-user', 1, 'root', 1365537600, 0, 'Привет, мир!'),
	(6, 1, 'root', 2, 'test-user', 1421750068, 0, 'Это ответ на личное сообщение.');
/*!40000 ALTER TABLE `userMessage` ENABLE KEYS */;

-- Дамп структуры для таблица cms.userRight
DROP TABLE IF EXISTS `userRight`;
CREATE TABLE IF NOT EXISTS `userRight` (
  `module` char(30) NOT NULL,
  `groupId` varchar(255) DEFAULT NULL,
  `description` char(50) NOT NULL,
  `picture` char(16) DEFAULT NULL,
  PRIMARY KEY (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.userRight: 39 rows
/*!40000 ALTER TABLE `userRight` DISABLE KEYS */;
INSERT INTO `userRight` (`module`, `groupId`, `description`, `picture`) VALUES
	('menu.*', '250', 'Управление меню', NULL),
	('article.category', '250', 'Управление категориями статей', NULL),
	('article.article', '250', 'Редактирование статей', NULL),
	('user.group', '', 'Управление группами пользователей', 'userGroup'),
	('user.user', '250', 'Управление пользователями', 'user'),
	('section.*', '250', 'Управление секциями', NULL),
	('html.*', '250,200', 'Редактирование текстовых блоков', NULL),
	('comment.moderate', '250,200', 'Комментарии: модерирование', 'comment'),
	('setting.core', '250', 'Общие настройки', 'setting'),
	('setting.url', '250', 'Преобразование ссылок (ЧПУ)', 'url'),
	('menu.hidden', '250', 'Скрытое меню', 'menu'),
	('shop.import', '250', 'Магазин: импорт товаров', NULL),
	('shopContent.variant', NULL, 'Магазин: варианты (модификации) товаров', NULL),
	('faq.*', '250', 'Часто задаваемые вопросы', NULL),
	('form.*', '250', 'Управление формами', NULL),
	('user.replace', NULL, 'Режим подмены пользователя', NULL),
	('vote.*', '250', 'Управление опросами (голосование)', NULL),
	('chat.moderate', '250,200', 'Чат: модерирование', NULL),
	('demotivator.setting', '250', 'Демотиваторы: управление', NULL),
	('demotivator.moderate', '250,200', 'Демотиваторы: модерация', NULL),
	('catalog.layout', NULL, 'Каталог: управление макетами', NULL),
	('catalog.item', '250', 'Каталог: управление записями', NULL),
	('note.*', '250', 'Заметки, информация для администрации', 'info'),
	('notification.setting', NULL, 'Уведомления: настройки', 'notification'),
	('shopContent.product', '250', 'Магазин: товары', NULL),
	('template.*', '250', 'Управление шаблонами', 'template'),
	('devTool.*', NULL, 'Инструменты разработчика', 'tool'),
	('forum.moderate', '200', 'Форум: модерирация', NULL),
	('forum.category', NULL, 'Форум: управление разделами', NULL),
	('shopSetting.feature', '250', 'Магазин: характеристики товаров', NULL),
	('shopSetting.setting', '250', 'Магазин: настройки, характеристики, призводители', 'shopSetting'),
	('shopContent.category', '250', 'Магазин: категории', NULL),
	('shopContent.brand', NULL, 'Магазин: управление производителями', NULL),
	('map.*', NULL, 'Интерактивные карты Google', NULL),
	('language.*', NULL, 'Мультиязычность', 'language'),
	('payment.method', NULL, 'Настройка платёжных методов', 'creditCard'),
	('payment.log', NULL, 'Платежи: просмотр лога', NULL),
	('module.*', NULL, 'Установка и удаление модулей', 'module'),
	('chat.setting', NULL, 'Чат: настройки', NULL);
/*!40000 ALTER TABLE `userRight` ENABLE KEYS */;

-- Дамп структуры для таблица cms.vote
DROP TABLE IF EXISTS `vote`;
CREATE TABLE IF NOT EXISTS `vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(300) NOT NULL,
  `answer` text NOT NULL,
  `result` text NOT NULL,
  `ip` char(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.vote: ~0 rows (приблизительно)
/*!40000 ALTER TABLE `vote` DISABLE KEYS */;
INSERT INTO `vote` (`id`, `question`, `answer`, `result`, `ip`) VALUES
	(3, 'Знаете ли вы что такое медитация?', 'почти ничего не знаю\r|что-то слышал об этом\r|да, знаю, но не практикую\r|иногда практикую\r|медитация занимает значительную часть моей жизни', '0|0|0|0|0', '');
/*!40000 ALTER TABLE `vote` ENABLE KEYS */;

-- Дамп структуры для таблица cms.widget
DROP TABLE IF EXISTS `widget`;
CREATE TABLE IF NOT EXISTS `widget` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `groupId` tinyint(3) unsigned DEFAULT NULL,
  `name` char(25) DEFAULT NULL,
  `data` varchar(2000) DEFAULT NULL,
  `cache` int(10) unsigned NOT NULL DEFAULT '0',
  `title_ru` char(35) DEFAULT NULL,
  `publicTitle` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `section` char(20) NOT NULL,
  `title_en` char(35) DEFAULT NULL,
  `cssClass` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `section` (`section`)
) ENGINE=MyISAM AUTO_INCREMENT=503 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.widget: 16 rows
/*!40000 ALTER TABLE `widget` DISABLE KEYS */;
INSERT INTO `widget` (`id`, `groupId`, `name`, `data`, `cache`, `title_ru`, `publicTitle`, `section`, `title_en`, `cssClass`) VALUES
	(6, NULL, 'shopProductGroup', '1', 30, 'Избранные товары', 1, 'bottom', 'Favorite Products', NULL),
	(14, NULL, 'shopCart', 'a:3:{s:7:"product";b:1;s:5:"total";b:1;s:8:"checkout";b:1;}', 0, 'Корзина', 1, 'right', 'Cart', NULL),
	(15, NULL, 'shopCategory', '', 0, 'Категории', 1, 'right', 'Categories', NULL),
	(9, NULL, 'comment', '', 0, 'Комментарии', 1, 'bottom', 'Comments', NULL),
	(10, NULL, 'demotivatorLast', '', 10, 'Последний демотиватор', 1, 'right', 'Last Demotivator', NULL),
	(11, NULL, 'html', 'right.2', 0, 'Демотиватор (текст)', 0, 'right', 'Демотиватор (текст)', NULL),
	(51, NULL, 'html', 'footer.1', 0, 'Копирайты', 0, 'footer', 'Копирайты', NULL),
	(13, NULL, 'articleBlog', 'a:4:{s:10:"categoryId";s:1:"2";s:8:"linkType";s:4:"list";s:12:"countPreview";i:0;s:9:"countLink";i:100;}', 0, 'Статьи', 1, 'right', 'Articles', NULL),
	(17, NULL, 'form', '1002', 30, 'Обратный звонок', 1, 'right', 'Callback', NULL),
	(58, NULL, 'articleBlog', 'a:4:{s:10:"categoryId";s:1:"1";s:8:"linkType";s:4:"blog";s:12:"countPreview";i:3;s:9:"countLink";i:0;}', 0, 'Новости', 1, 'right', 'News', NULL),
	(53, NULL, 'html', 'bottom.1', 0, 'С праздником!!!!', 1, 'bottom', 'Happy Holidays!!!', NULL),
	(54, NULL, 'vote', '3', 10, 'Голосование', 1, 'right', 'Poll', NULL),
	(68, NULL, 'catalogSearch', 'a:2:{s:2:"id";s:1:"1";s:3:"fld";a:2:{s:5:"title";b:1;s:4:"year";a:4:{s:3:"min";d:1900;s:3:"max";d:2015;s:4:"step";d:1;s:5:"range";b:1;}}}', 0, 'Поиск', 1, 'top', 'Search', NULL),
	(63, NULL, 'oauth', 'a:2:{s:8:"register";b:0;s:2:"vk";a:2:{i:0;s:3:"111";i:1;s:3:"222";}}', 0, 'Войти через...', 0, 'top', 'Войти через...', NULL),
	(66, NULL, 'html', 'right.3', 0, 'Произвольный текст', 0, 'right', 'Произвольный текст', NULL),
	(77, NULL, 'map', 'a:6:{s:2:"id";i:68;s:14:"centerLatitude";d:43.133643865585;s:15:"centerLongitude";d:55.958936550308998;s:4:"zoom";i:10;s:4:"type";s:7:"ROADMAP";s:6:"marker";a:1:{i:0;a:3:{s:5:"title";s:18:"Я живу тут";s:8:"latitude";d:43.084396439491002;s:9:"longitude";d:55.973300787063003;}}}', 0, 'Как проехать', 1, 'bottom', 'How Can I Get To', NULL);
/*!40000 ALTER TABLE `widget` ENABLE KEYS */;

-- Дамп структуры для таблица cms.widgetType
DROP TABLE IF EXISTS `widgetType`;
CREATE TABLE IF NOT EXISTS `widgetType` (
  `name` char(20) NOT NULL,
  `title` char(35) NOT NULL,
  `controller` char(20) NOT NULL,
  `action` char(20) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Дамп данных таблицы cms.widgetType: 19 rows
/*!40000 ALTER TABLE `widgetType` DISABLE KEYS */;
INSERT INTO `widgetType` (`name`, `title`, `controller`, `action`) VALUES
	('html', 'Произвольный текст', 'html', 'widgetHtml'),
	('articleBlog', 'Блог (анонс статей в категории)', 'article', 'widgetBlog'),
	('menu', 'Меню', 'menu', 'widgetList'),
	('shopFeatureSearch', 'Магазин: поиск по характеристикам', 'shopSetting', 'widgetFeatureSearch'),
	('shopCategory', 'Магазин: категории', 'shopSetting', 'widgetCategory'),
	('shopCart', 'Магазин: корзина', 'shopSetting', 'widgetCart'),
	('form', 'Контактная форма', 'form', 'widgetForm'),
	('shadowbox', 'Всплывающие изображения (shadowbox)', 'shadowbox', 'widgetShadowBox'),
	('vote', 'Голосование', 'vote', 'widgetVote'),
	('comment', 'Комментарии', 'comment', 'widgetComment'),
	('chat', 'Чат', 'chat', 'widget'),
	('demotivatorLast', 'Демотиваторы: последний добавленный', 'demotivator', 'widgetLast'),
	('catalogSearch', 'Каталог: поиск', 'catalog', 'widgetSearch'),
	('oauth', 'Регистрация и авторизация OAuth 2.0', 'oauth', 'widget'),
	('shopProductGroup', 'Магазин: группа товаров', 'shopSetting', 'widgetProductGroup'),
	('map', 'Карта Google', 'map', 'widgetMap'),
	('articleList', 'Список статей в категории', 'article', 'widgetList'),
	('divOpen', 'DIV: открывающий блок', 'div', 'widgetOpen'),
	('divClose', 'DIV: закрывающий блок', 'div', 'widgetClose');
/*!40000 ALTER TABLE `widgetType` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

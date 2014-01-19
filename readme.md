[![bbDKP](http://www.bbDKP.com/images/site_logo.png)](http://www.bbDKP.com)

#bbTips v1.1

BBCode Tooltip mod. 

WowHead-style Tooltips (Item, Craft, Spell, Quest, Achievements, npc, wowchar, itemset, item icon, item dkp with PTR support.

BBcodes get installed automatically on your Board

### Example BBcode usage

####Item
`[item gems="40133" enchant="3825"]50468[/item]`

`[item gems="40133" enchant="3825"]50468[/item]`

######PTR usage
You can prefix the tags with "ptr" to access the Wowhead Public Test Realm database.

Example for PTR 4.2

`[ptritem]Decimation Treads[/ptritem]`

`[ptritem]Sho'ravon, Greatstaff of Annihilation[/ptritem]`
 
####Item icon
 
`[itemico gems="40133" enchant="3825"]50468[/itemico]`

`[itemico gems="40133" enchant="3825" size=small]Ardent Guard[/itemico]`

`[itemico gems="40133" enchant="3825" size=medium]Ardent Guard[/itemico]`

`[itemico gems="40133" enchant="3825" size=large]Ardent Guard[/itemico]`

####Itemset

`[itemset]Sanctified Ymirjar Lord's Plate[/itemset]`

####Achievements

`[achievement]Breaking Out of Tol Barad[/achievement]`

`[achievement]4874[/achievement]`

`[ptrachievement]Explore Hyjal[/ptrachievement]`

`[achievement]Loremaster of Outland[/achievement]`

####Spells: 

As of Wow 4.0, the spell ranks were removed. Existing spell tags with spell rank will ignore the rank argument.

`[spell]Power Word: Shield[/spell]`
`[spell]Master of Beasts[/spell]`

in bbTips 1.1, recipes, guild perks and Glyph spells can be used.

`[spell]Weak Troll's Blood Elixir[/spell]`
`[spell]Mr. Popularity[/spell]`
`[]spell]Glyph of Barkskin[/spell]`

####Quests

`[quest]A Dire Situation[/quest]`

####NPC bbCode

You can select any npc 
`[npc]Illidan Stormrage[/npc]`

including the new Pandaria Battle pets :

`[npc]Adder[/npc]`


####Crafting bbcode. 
The Craft bbcode can be used with or without the mats argument. The mats argument can be used only once per post.

`[craft mats]Recipe: Dirge's Kickin' Chimaerok Chops[/craft]`

`[craft mats]Design: Brazen Elementium Medallion[/craft]`

`[craft mats]Schematic: Hard Khorium Goggles[/craft]`

`[craft mats]Recipe: Destruction Potion[/craft]`

`[craft]Plans: Black Felsteel Bracers[/craft]`

`[craft mats]Recipe: Vial of the Sands[/craft]`

## Current
v1.1 DEV

## Installation

#### Requirements
1.	phpbb 3.0.12 
2.	bbDKP 1.3.0 or higher
3.	ftp and founder admin access on your phpbb installation.

#### MODX installation
*	If you have a previous bbTips, first uninstall it with Automod.
* 	Unzip the zip file into /store/mods
* 	Launch automod, choose the install link. this will copy all the files, perform the necessary edits.
* 	Then surf to /install/index.php, and you will see the database installer. Launch the database installer.  This will install the acp module, and clear the caches (template, theme, imagesets)
*	Once installed, you will find the ACP module added under the raid section in bbdkp ACP.


## Changelog
-	2014-01-19 : 1.1
	-	compatible with bbDKP 1.3.0
	
-	2013-07-30 : 1.0.3
	-	repaired xml install files


-	2012-12-26 : 1.0.2
	-	updated to phpBB 3.0.11 
	-	wowhead local.js power.js updated for local usage.
	-	achievement bbcode fixed, wowhead now uses htmlentities
	-	Spell bbcode now support Glyphs, Recipes and Guild-perks
	-	SimplephpDom 1.5 updated to revision 202 of 10/10/2012
	-	css changes : quests, spells now appear in #FAB008. 
	
-	2012-08-05 : 1.0.1
	-	[FIX] #208 fix for incorrect json in wowhead sourcecode	
		
-	2012-06-03 : 1.0.0
	-	[NEW] MSSQL / postgresql support	
	-	[NEW] plugin manager compatible	
	-	[FIX] removed old dkp_admin.php from language folder	
-	2011-12-14 : 0.4.3
	-	[CHG] crafting redone. The nomats argument is gone, you have to select mats if you want to see materials. works with Blacksmithing, Alchemy, Leatherworking, Jewelcrafting, cooking, Engineering.
	-	[FIX] quick reply now works when setting to locally hosted power.js	
	
-	2011-10-16 : 0.4.2
	-	[FIX] renamed installer to index.php
	-	[FIX] does a trim of the received text before testing it for html	-	[FIX] randomly enchanted items are now recognised (example http://www.wowhead.com/item=31940)		

	
-	2011-06-19 : 0.4.1
	-	[NEW] Added PTR support
	-	[FIX] Fixed installation error due to auth link missing in acp info	-	[FIX] memory leak in get object from cache fixed.	-	[FIX] parse.php reference to undefined $config after mod uninstallation fixed.	-	[FIX] removed unused functions, updated var usage in classes to private/public	
-	2011-04-01 : 0.4.0
	-	[CHG] requires any previous bbtips to be fully uninstalled.
	-	[CHG] will install under Raids tab if bbDKP 1.2.1 is installed, or else under the MODS tab.				
	-	[CHG] is no longer a plugin but a selfstanding mod.	-	[DEL] removed wowchar bbcode: The blizzard armory is no longer functioning.				
	
-	2010-10-17 : 0.3.8
	-	[NEW] fix in itemdkp. was not shown		
-	2010-10-03 : 0.3.7
	-	[NEW] wowchar tooltips now show the enchants and the gemming	-	[FIX] #62 Realm and region now derived from bbdkp settings acp bbtips_realm	and bbtips_region config removed				-	[FIX] #53 caching function now checks its input properly
	-	[NEW] enchant and gem support for item and itemico :  example [itemico gems="40133" enchant="3825"]Ardent Guard[/itemico] [item gems="40133" enchant="3825"]50468[/item]
	-	[NEW] better icon sizing :  [itemico size=small]Ardent Guard[/itemico] [itemico size=medium]Ardent Guard[/itemico] [itemico size=large]Ardent Guard[/itemico]		
	-	[NEW] DE/FR translation
		
-	2010-06-19 : 0.3.6<br />
	-	[FIX] independent of wowhead status. if down then new tags requests will get a "not found" message
	-	[NEW] itemset aligns better in the portal, bbcode added to installer
	-	[NEW] NPC bbcode added
	-	[FIX] usage of http urls in bbtips tags is blocked
	-	[FIX] wowchar bbcode now supports spaces in realmname. you have to put underscores . Example : [wowchar realm=Chamber_of_Aspects region=EU]Adlet[/wowchar]		
-	2010-06-06 : 0.3.5<br />
	-	[NEW] WoW character bbcode. example :  [wowchar realm=Lightbringer region=EU]Phyrra[/wowchar]. Jquery Overlay with 3 tabs.
	-	[NEW] tab switching with mouseover.
	-	[upd] updated Jquery Tools to 1.2.2
	-	[FIX] fixed the css for wowhead, icon frames are again visible
		
-	2010-05-30 : 0.3.4<br />
	-	[chg] moved overall_header.html to dkp_header.html
	-	[FIX] fixed quest, spell bbcodes
	-	[FIX] fixed item, itemico, craft, itemset wowhead utl changed (added /wow in path)
	
-	2010-04-13 : 0.3.3<br />
	-	[FIX] parser fixed. bbcodes without attributes parsed firstly
	-	[UPD] language file, html, acp adapted		
			
-	2010-04-11 : 0.3.2<br />
	-	[FIX] Achievements, quests, spells, itemsets, items, npc query by name fixed for new Wowhead json structure
	-	[UPD] power.js updated for local hosting
	-	[NEW] now uses a DOM parser instead of Regex
	-	[FIX] Ticket 21 : Wowhead url and searchpage change
	-	[FIX] Ticket 20 : problem in bbDKP item acp 	-	[FIX] Ticket 11 : cannot redeclare class wowhead : redid the parse class. 	-	[NEW] subsilver2 support
		
-	2010-02-07 : 0.3.1<br />
	-	[NEW] class structure refit
	-	[NEW] bbtips in dkp viewitem, listitems, viewraid, viewevent
	-	[NEW] bbtips in loot block
	-	[NEW] bbtips in list items ACP
	-	[NEW] subsilver2 support
		
-	2009-12-21 : 0.1-initial release<br />
	-	[CHANGE] The former Itemstats has been merged with wowarmory tooltips

## License

[GNU General Public License v2](http://opensource.org/licenses/gpl-2.0.php)

This application is opensource software released under the GPL. Please see source code and the docs directory for more details. Powered by bbDkp (c) 2009 The bbDkp Project
If you use this software and find it to be useful, we ask that you retain the copyright notice below. While not required for free use, it will help build interest in the bbDkp project and is required for obtaining support.
bbDKP (c) 2008, 2009 Sajaki, Malfate, Kapli, Hroar
bbDKP (c) 2007 Ippeh, Teksonic, Monkeytech, DWKN
EQDkp (c) 2003 The EqDkp Project Team 

## Credits
Blazeflack and Twizted, for their work and support on the Apply plugin. 

## Paypal donation

[![Foo](https://www.paypal.com/en_US/BE/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=sajaki9%40gmail%2ecom&lc=BE&item_name=bbDKP%20Guild%20management&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted)



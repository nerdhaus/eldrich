# About This Project

[Eldrich Host](http://eldrich.host) is a public repository of information about [Eclipse Phase](http://eclipsephase.com),
PostHuman Studios' excellent (and Creative-Commons licensed) tabletop roleplaying game. It mashes up classic AI Apocalyps
science fiction with Lovecraftian horror and trippy Transhumanism. Players generally assume the roles of secret agents
(or terrorists, depending on who you ask) tasked with protecting the remnants of humanity from assorted existential horrors.

Eldrich Host is intended to be a one-stop shop for canon and homebrew game data: NPC and PC charsheets; locations
and factions in the game; gear, skill, and creature data; etc. It isn't designed to be a character generation tool
or a *rule* reference to Eclipse Phase. Rather, it's meant to be a streamlined, mobile-friendly place for GMs and
players to keep track of the million or so moving parts that make up the average game session. In particular, Eldrich
Host makes a best effort to pre-calculate the assorted bonuses that different skills, traits, and types of equipped
gear give to the different actions that players can perform.

## Current Features
- Full data for Character Aptitudes, Skills, Traits, Mental Derangements, and Psi Sleights.
- Basic writeups for Factions, Locations, and noteworthy strains of the Exsurgent virus.
- Complete gear catalog including Morphs, Weapons, AIs, Drugs, and more.
- Full character sheets for NPCs and pregen PCs from every EP core book, splatbook, and adventure.
- Full data on every creature and robot found in the core books, adventures, X-Threats reference books.
- Full player charsheet storage, with (passable) support for multiple morphs and identities per character.
- Fast site-wide search, and special gear browsing tools to find what you're looking for fast. 
- Mobile-friendly browsing and reference tools for all data (including character sheets).
- Printer-friendly views of every piece of data on the site
- Full book-and-page citation information for every piece of information.
- Support for homebrew variations on all game data. Save and share your campaign's NPCs, your custom exsurgent strains, etc.

Other features are coming, but that's what's here for version 1.0.


## The Tech

Eldrich Host is built on Drupal 8, a fairly flexible open-source CMS with rich content modeling tools. It gave us
enough flexibility to implement Eclipse Phase's extremely tangly gear information — in which a player's body, the scope
on their rifle, and a simulated copy of themselves running in a thumbdrive in their pocket are all technically "gear".

This project includes a Vagrantfile that can bootstrap a development server and configure an (empty) copy of the site,
but real dev work requires getting a scrubbed snapshot of the site's live database. The custom Drupal modules built
for Eldrich Host consist of:

### Site specific junk
- **EP Schema**, an exported pile of configuration data that defines all of the content types, their fields, and
their relationships to each other.
- **EP Import** is a set of migration scripts, custom import processors, and preprocessing tools that import a megabyte
or so of CVS data to populate the site's content. It covers "core" information like attributes, skills, traits, and so 
on as well as the initial set of NPCs, PCs, campaigns, and news posts that were on the site when it launched. This will
likely fall by the wayside now that the site is live, but it's potentially useful for anyone who's building out EP tools
in the future.
- **EP Game Tools**, a module that defines a bunch of handy bits for GMs and players like spreadsheets of skills for
all the players in a given campaign, random name generators that know about EP's various factions and nationalities,
and so on.
- **Eldrich**, a grab bag of utility code that enforces various business rules and handles calculating assorted skill,
stat, weapon, and armor bonuses based on a character's equipment.
- **GMO**, short for 'Game Master Only' — it enforces access control on content throughout the site to prevent players
from seeing secret information about the campaign they're currently participating in.

### Custom data types
- **Integer time** is a field formatter that lets time spans (5 seconds, 3 weeks, etc) be stored in simple int fields.
Its formatter also works with integer range fields, translating them into friendly timespans like "3-5 seconds".
- **Operation** is a field type that that stores a decimal number *and* a math operation that goes with it. it also
provides helper functions for adding together a pile of numbers and operations to get a result. It's how Eldrich stores
things like damage values, armor penetration values, and so on for weapons. Storing them as Operations makes it easy
for a particular weapon to do 6 damage while a particular ammo, say, halves the damage but doubles the armor penetration.
- **EP Statblock** is a custom Drupal field type that stores Eclipse Phase stat/aptitude data. When used on NPCs, PCs,
creatures, and so on the Statblock field usually indicates their base ability scores. When used on gear, morphs, etc it
represents the bonuses to the abilities given by the gear.
- **EP Skill Reference** is a derivative of Drupal's popular Entity Reference field type. It's used to assign skill values
(and bonuses) to players, by storing a reference to a Skill entity along with Field, Specialization, and Points Spent
data.
- **EP Complex Values** is a set of utility fields that store things like weapon ranges, movement speeds, and armor
values in single fields. In theory all of those things could've been modeled with simple Intger fields, but this kept
things much tidier.

### Presentation
*Veil*, Eldrich Host's custom front-end theme, is based on Bootstrap 3.2 nd handles most of the heavy lifting of making
things look nice. It makes heavy use of Twig template inheritance to handle the special needs of various gear types, and
completely bypasses normal FieldAPI rendering for a lot of the tangly Eclise Phase data. Skills, Weapons, and Armor in
particular are never output as fields directly — they're used as the starting point for dynamically calculating stat
values that include all of a character's current bonuses and so on.

Veil has a lot of hard dependencies on *Eldrich* and *EP Game Tools* — it makes plenty of assumptions about those
modules and their support classes being there, so it can't really be untangled.
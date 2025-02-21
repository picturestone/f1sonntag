##### unreleased

#### 2024-07-22 1.5.2

-   Rounded best of race scores to 2 decimal places when showing it in the frontend. [#40]

#### 2024-05-09 1.5.1

-   Fixed bug where users with null score would lead the season scoring table. [#39]

#### 2024-05-09 1.5.0

-   Fixed bug where race result bets would not be shown after race start as long as no race results were entered. [#38]
-   Added new role for bets editing. [#37]
  - Set the `roles` column in the `user` table to `["ROLE_ADMIN","ROLE_BETS_EDIT"]` for users which should be able to change bets.

#### 2024-05-06 1.4.0

-   Added betting editing for other users in admin. [#36]

#### 2024-05-02 1.3.0

-   Sorted race result bets detail and world championship list by user name, sorted user result bets by race date. [#35]

#### 2024-05-02 1.2.0

-   Removed unnecessary table heading from user results bet list. [#34]

##### 2024-05-02 1.1.1

-   Removed twig caching leading to bets not being shown correctly. [#33]

##### 2024-05-02 1.1.0

-   Added server config. [#32]

##### 2024-05-01 1.0.0

-   Hid bets from other users in user results bet detail view if betting timeout hasnt passed yet. [#31]
-   Fixed race form styling. [#30]
-   Locked world championship after adding. [#29]
-   Improved forms and login screen. [#28]
-   Added user change password. [#27]
-   Added time constraints. [#26]
-   Added world champion bets. [#25]
-   Added user result details. [#24]
-   Added user result overview. [#23]
-   Added point calculation. [#22]
-   Added race result bets. [#21]
-   Added delete modals in admin and deleting cascades. [#20] 
-   Removed WorldChampion entity, added world champion relation to season. [#19]
-   Refactored entities, added WorldChampion entity. [#18]
-   Added flash messages for user feedback in admin. [#17]
-   Added punishment points entries editing in admin. [#16]
-   Added race result entries editing in admin. [#15]
-   Added punishment points admin. [#14]
-   Renamed admin routes, changed admin template paths. [#13]
-   Added user admin. [#12]
-   Added race results admin. [#11]
-   Added active state for driver. [#10]
-   Updated dev install instructions. [#9]
-   Added races admin. [#8]
-   Added season admin. [#7]
-   Added driver admin. [#6]
-   Added nav. [#5]
-   Added team admin. [#4]
-   Added entities. [#3]
-   Added users. [#2]
-   Added setup. [#1]

# yii2-comus
 
## Comment Module for Yii2 PHP Framework

**Comus** is a complete comment module for the [Yii 2.0](https://www.yiiframework.com/ "Yii") PHP Framework.

It lets authenticated visitors of a website place comments. The moderator, as well as the administrator of the site
can accept or reject comments, either after or before they are submitted. They can also
update or delete any comment. Optionally, commenters are allowed to update or delete
their own comment. 

**Comus** sports three widgets:
 - **Comment** Displays a comment block with all the comments issued on a certain
 model (the *subject*). Intended to be used in a view file.
 - **CommentCount** Displays the count of comments issued on a subject. Might be 
 used in an index file.
 - **UserComments** Displays links to all comments issued by a certain user, on several
 subjects.

A demonstration of **Comus** is [here](https://demo.sjaakpriester.nl/comus).

## Prerequisites ##

**Comus** relies on [Role-Based Access Control](https://www.yiiframework.com/doc/guide/2.0/en/security-authorization#rbac "Yii2")
 (RBAC). Therefore, the [`authManager`](https://www.yiiframework.com/doc/api/2.0/yii-base-application#$authManager-detail "Yii2")
 application component has to be configured. **Comus** works with Yii's `PhpManager` as well as 
 with the `DbManager`.
 
**Comus** assumes that the site uses [Bootstrap 4](https://www.yiiframework.com/extension/yiisoft/yii2-bootstrap4).
 I suppose that it will work under Bootstrap 3, but it will be a lot less pleasing to the eye.
 
**Comus** also assumes that [Font Awesome 5.x](https://fontawesome.com/) is available, either
the Free or the Pro version. If you're adventurous, you may adapt the [`icons` option](#module-options)
 of the module to make **Comus** work with another icon font.
 
 It is strongly advised that the app's uses 
 [Pretty URLs](https://www.yiiframework.com/doc/guide/2.0/en/runtime-routing#using-pretty-urls).
 
## Installation ##

Install **yii2-comus** in the usual way with [Composer](https://getcomposer.org/). 
Add the following to the require section of your `composer.json` file:

`"sjaakp/yii2-comus": "*"` 

or run:

`composer require sjaakp/yii2-comus` 

You can manually install **yii2-comus** by [downloading the source in ZIP-format](https://github.com/sjaakp/yii2-comus/archive/master.zip).
 
#### Module ####

**Comus** is a [module](https://www.yiiframework.com/doc/guide/2.0/en/structure-modules#using-modules "Yii2")
 in the Yii2 framework. It has to be configured 
in the main configuration file, usually called `web.php` or `main.php` in the `config`
directory. Add the following to the configuration array:

    <?php
    // ...
    'modules' => [
        'comment' => [
            'class' => 'sjaakp\comus\Module',
            // several options
        ],
    ],
    // ...


The module has to be *bootstrapped*. Do this by adding the following to the
application configuration array:

    <php
    // ...
    'bootstrap' => [
        'comment',
    ]
    // ...

There probably already is a `bootstrap` property in your configuration file; just
add `'comment'` to it.

**Important**: the module should also be set up in the same way in the console configuration (usually
called `console.php`).

#### Console commands ####

To complete the installation, two [console commands](https://www.yiiframework.com/doc/guide/2.0/en/tutorial-console#usage "Yii2")
 have to be run. The first will create a database table for the comments:
  
    yii migrate
    
The migration applied is called `sjaakp\comus\migrations\m000000_000000_init`.
    
The second console command is:
 
    yii comus
    
This will set up a role and several permissions in the RBAC-system.

## Basic usage ##

Say we have a site presenting information about books and about movies. We define
`ActiveRecord`s Book and Movie. In the `view` file for the Book model, we can use the 
**Comment** widget like so:

       <?php
       // views/book/view.php
       use sjaakp\comus\Comment;
       
       /* @var app\models\Book $model */
       ?>
       
       <?= $model->title ?>
       <?= $model->author ?>
       // ... more information about $model ...
       
       <?= Comment::widget([
            'model' => $model
       ]) ?>
       
Likewise in the `view` file for the Movie model.

In the `index` files we might use the **CommentCount** widget:

       <?php
       // views/book/index.php
       use sjaakp\comus\CommentCount;
       use yii\grid\GridView;
       
       /* @var yii\db\ActiveDataProvider $dataProvider */
       ?>
       
       <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'title:text',
                [
                    'value' => function ($model, $key, $index, $widget) {
                        return CommentCount::widget([ 'model' => $model ]);
                    },
                    'format' => 'html'  // CommentCount displays a link
                ],
                // ... more columns ...
            ],
            // ... more options ...
       ]) ?>

The site may also provide in displaying information about registered users. The `view` file
for the User model can use the **UserComments** widget and may look like this:

       <?php
       // views/user/view.php
       use sjaakp\comus\UserComments;
       
       /* @var app\models\User $user */
       ?>
       
       <?= $user->name ?>
       <?= $user->registeredSince ?>
       // ... more information about $user ...
       
       <?= UserComments::widget([
            'userId' => $user->id
       ]) ?>
       
#### Comment levels ####

A comment directly on a subject is said to be of *level 0*. A comment on such comment is
said to be a *reply* or a comment of *level 1*. And so on. The maximum level of a comment 
is settable in the configuration. Default is 0, meaning that only direct comments are possible.
Don't set the maximum level to high, if only because each level indents somewhat.

Notice that if the moderator deletes a comment, all of its replies are deleted as well.
       
##### Comment block as seen by guest user ##### 

![Comment Block](img/block-guest.gif?raw=true "Comus' comment block as seen by guest")

Comments by moderators are distinguished by a light background color. Guest is not able to issue a comment, 
she should login first.
       
##### Comment block as seen by authenticated user ##### 

![Comment Block](img/block-user.gif?raw=true "Comus' comment block as seen by authenticated user")

His own comments have a light cyan background color. Hovering over a comment shows a
button to issue a reply on it (if maxLevel > 0).
       
##### Comment block as seen by moderator ##### 

![Comment Block](img/block-moderator.gif?raw=true "Comus' comment block as seen by moderator")

Content of rejected comments is readable. Hovering over a comment shows several
buttons to manage it.

##### Buttons #####

![Buttons](img/buttons.gif?raw=true "Comus' buttons")

From left to right:
 - **accept/reject** comment
 - **previous/next** pending comment
 - **update** comment
 - **delete** comment
 - **reply** to comment
 
 Which buttons appear on hovering above a comment, depends on the permissions of the user, and on the status
 of the comment.
 
## Permissions ##

**Comus** defines a few [Permissions](https://www.yiiframework.com/doc/guide/2.0/en/security-authorization#rbac).
They are:

 |Name|Description|
 |---|---|
 |**createComment**|Issue a comment|
 |**updateComment**|Update a comment|
 |**deleteComment**|Delete a comment|
 |**updateOwnComment**|Update self-created comment|
 |**deleteOwnComment**|Delete self-created comment|
 |**manageComments**|Accept or reject comments|

One Role is defined: **moderator**. Associated Permissions are **updateComment**, 
**deleteComment**, and **manageComments**.

If the site sports a Role **admin**, the Role **moderator** is included.

Only moderators are allowed to view the comments overview, on URL:

    example.com/comment
    
From there, they can easily jump to pending records, waiting to be accepted or rejected.

**Notice** that in the default configuration, **Comus** allows *all* authenticated users to
create comments, and it's not necessary to explicitly give them permission. The **createComment**
Permission is for scenarios where you want to restrict the allowance to issue comments.
  
## Module options ##

The **Comus** module has a range of options. They are set in the application 
 configuration like so:
 
     <?php
     // ...
     'modules' => [
         'comment' => [
             'class' => 'sjaakp\comus\Module',
             'maxLevel' => 2,
             // ...
             // ... more options ...
         ],
     ],
     // ...
     
The options (most are optional) are:

 - **loginUrl** `string|array` Url to the login page of the site in the usual Yii-format.
  Not optional; must be set.
 - **profileUrl** `array|null|false` Url of the profile view. Will be extended with 
    `'id' => <user-id>`. 
    - `array` Profile is defined elsewhere on the site. Example: `['/profile/view']`.
    - `null` (default) Uses **Comus**' builtin light weight profile.
    - `false` User names will never be presented as a link, just as plain text.
 - **maxLevel** `int` Maximum depth of comment. If `0` (default) comments can only 
    be issued directly on the subject, not on another comment
 - **orderDescending** `bool` Whether comments are presented in descending order. Default: `true`.
 - **showPending** `bool` whether pending comments are visible. Doesn't effect user with
  `'manageComments'` permission; she can always view pending comments. Default: `true`.
 - **showRejected** `bool` whether rejected comments are visible. Notice that for ordinary users,
  a message is displayed and the actual contents of the comment are hidden. Doesn't effect 
  user with `'manageComments'` permission; she can always view rejected comments 
  and their content. Default: `true`.
 - **showOwn** `bool` Overrides `showPending` and `showRejected` for comments created by 
 the user herself. Example: if you want rejected comments to be hidden, accept for the user's 
 own comments, set `showRejected = false` and `showOwn = true`. Strongly recommended if you
 set `showPending = false`; otherwise the user won't see that she has issued a comment and probably try again.
 - **datetimeFormat** `string` `'standard'` | `'relative'` | any value 
 [`yii\i18n\formatter::datetimeFormat`](https://www.yiiframework.com/doc/api/2.0/yii-i18n-formatter#$datetimeFormat-detail)
  can take. Default is `'standard'`, which yields a combination of `'relative'` and `'short'`.
  - **viewPermission** `null | string`. RBAC-permission needed to view comments. If `null`
   (default): all users can view comments, including guests.
  - **createPermission** `null | string`. RBAC-permission needed to create comments. If `null`
   (default): all authenticated users can create comments.
  - **usernameAttr** `string | callable`. Not optional; must be set. Default: `'name'`.
    - `string`: attribute of username (nickname) 
    in [identity class](https://www.yiiframework.com/doc/api/2.0/yii-web-identityinterface).
    - `callable`: `function($identity)` returning username.
  - **avatarAttr** `null | string | callable`. Default: `null`.
    - `string`: attribute of avatar image
    in [identity class](https://www.yiiframework.com/doc/api/2.0/yii-web-identityinterface).
    - `callable`: `function($identity)` returning avatar image.
    - `null`: no avatar is shown.
 - **maxLength** `int` Maximum length of comment, in characters. Default: `400`.
 - **truncLength** `int` Maximum length of comment fragment presented
  in **UserComments** widget and in `default/index` action,
 in characters. Default: `80`.
 - **icons** `array` Presets for a number of icons **Comus** uses. Default: see source.
 
 
## Comment Options ##

The **Comment** widget has three options:

 - **model** `ActiveRecord` The model the comment block is related to.
 - **subject** `string` Basically, the relative URL of the `view` file. Either **model**
 or **subject** must be set, preferably **model**.
 - **options** `array` The HTML options for the surrounding `div`. Default: `[]`.
 
## CommentCount Options ##

The **CommentCount** widget has four options:

 - **model** `ActiveRecord` The model the comment count is related to.
- **subject** `string` Basically, the relative URL of the `view` file. Either **model**
 or **subject** must be set, preferably **model**.
 - **showZero** `bool` Whether the widget should display if there are no comments related to
  the subject. Default: `false`.
 - **template** `string` HTML template for the output. Default: see source.
 
## UserComments Options ##

The **UserComments** widget has two options:

 - **userId** `int` The ID of the user who issued the comments.
 - **datetimeFormat** `string` `'standard'` | `'relative'` | any value 
  [`yii\i18n\formatter::datetimeFormat`](https://www.yiiframework.com/doc/api/2.0/yii-i18n-formatter#$datetimeFormat-detail)
   can take. If not set, it takes the setting of the module. Default is `'short'`.
   
**UserComments** is derived from Yii's `GridView`, so it has al its options as well. 
 
## Events ##

**Comus** doesn't define [Events](https://www.yiiframework.com/doc/guide/2.0/en/concept-events)
 of it's own. However, a Comment is just an ordinary `ActiveRecord` and you can use 
[it's Events](https://www.yiiframework.com/doc/api/2.0/yii-db-baseactiverecord#EVENT_AFTER_DELETE-detail) 
to intercept comment events and inject your own code. 
Refer to the file 'ComusEvents.php' for a possible approach.
 
## Internationalization ##

 All of **Comus**' utterances are translatable. The translations are in the `'sjaakp\comus\messages'`
  directory.
  
 You can override **Comus**' translations by setting the application's 
  [message source](https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n#2-configure-one-or-multiple-message-sources "Yii2")
  in the main configuration, like so: 
 
     <?php
     // ...
     'components' => [
         // ... other components ...     
         'i18n' => [
              'translations' => [
                   // ... other translations ...
                  'comus' => [    // override comus' standard messages
                      'class' => 'yii\i18n\PhpMessageSource',
                      'basePath' => '@app/messages',  // this is a default
                      'sourceLanguage' => 'en-US',    // this as well
                  ],
              ],
         ],
         // ... still more components ...
     ]
 
 The translations should be in a file called `'comus.php'`.
 
 If you want a single or only a few messages translated and use **Comus**' translations for 
  the main part, the trick is to set up `'i18n'` like above and write your translation file
  something like:
  
      <?php
      // app/messages/nl/comus.php
      
      $comusMessages = Yii::getAlias('@sjaakp/comus/messages/nl/comus.php');
      
      return array_merge (require($comusMessages), [
         'Empty' => 'Leeg',   // your preferred translation
      ]);
 
 
 At the moment, the only language implemented is Dutch. Agreed, it's only the world's
  [52th language](https://en.wikipedia.org/wiki/List_of_languages_by_number_of_native_speakers "Wikipedia"),
  but it happens to be my native tongue. Please, feel invited to translate **Comus** in 
  other languages. I'll be more than glad to include them into **Comus**' next release.
  
## Override view-files ##

Any of **Comus**' view files can be overridden. 
Just set the **views** setting of the module to something like:
 
     <?php
     // ...
     'modules' => [
         'comment' => [
             'class' => 'sjaakp\comus\Module',
             'views' => [
                  'default' => [    // Comus controller id
                      'user' => <view file>    // action => view
                  ]
             ],
             // ...
             // ... more options ...
         ],
     ],
     // ...

`<view file>` can be of any form
  [`yii\web\controller::render()`](https://www.yiiframework.com/doc/api/2.0/yii-base-controller#render()-detail "Yii2")
  accepts.  

## Module ID ##

By default, the Module ID is `'comment'`. It is set in the module configuration. If necessary
(for instance if there is a conflict with another module or application component), you may set the Module 
ID to something different. **Important:** in that case, the `moduleId` property of the
 **Comment** and **CommentCount** widgets must be set to
this new value as well.

## Comus? ##

**Comus** is the ancient [Greek god of festivity and revelry](https://en.wikipedia.org/wiki/Comus).
He is the son of Dionysus and is associated with anarchy and chaos. That, and the fact
that his name starts with 'com', to me makes **Comus** a good name for a comment module.

![Comus](img/comus.jpg?raw=true "Comus and his rout by John Bell, ca. 1840")

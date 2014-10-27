Sushee CMS
==========

> A small and highly customizable content management system to build JSON APIs.

**WARNING: Work in progress!!**

About
-----

Sushee helps you to *not* waste time...
- building a custom web-application backend from scratch
- tailoring an existing solution to fit your needs
- applying security patches and vendor updates all the time
- reading through endless documentation & code

![alt text](https://raw.githubusercontent.com/chrigu-ebert/Sushee-CMS/master/web/admin/facebook.png "Logo")

Sushee has a few key features and principles to make life easier.

- It is **simple** to setup and maintain. It has almost no dependencies. It doesn't need all those time-consuming updates and you can backup your data easily by just copying the `data` folder. Check the [system requirements](#system-requirements) section for details.
- Very **small** codebase. There are only a few classes necessary to run the whole CMS. No rocket sience.
- You can **customize it** easily to fit your needs. You can add & remove data attributes on the fly. You can extend existing modules and/or build your own.
- It is really **fast** and performing well. All the usual vendor scripts bloat your application and make it slow but often you just need a fraction of all the things that are loaded. Sushee directly writes data to the filesystem. No extra database software necessary. Everything is loaded asynchronously with Javascript.
- It has a small footprint and is **open source** :)


### Built with
- [Gumby Framework](http://gumbyframework.com/) - Clean and sexy css framework
- [JQuery](http://jquery.com/) - The Javascript library we all know


Setup
-----

### System requirements
- PHP 5.2.17 or higher
- Write permissions in `/data/modules` folder

### Getting started
0. Clone/copy repository
0. Create a user
0. Create a module
0. Access admin panel `/web/admin`


Configuration
-------------

### User management

Managing users is quite easy. Edit the file `data/config/users.json` and provide a user-object for each account
that contains the following fields:

    {
        "name": "chrigu",
        "role": "user",
        "display_name": "Chrigu",
        "salt": "YOUR SALT",
        "hash": "YOUR HASH"
    }


Key | Value
--- | ---
name | The user id which must be unique for each user
role | The user role which is either `user` or `admin`. The admin role has some more options in the admin panel.
display_name | The name that is displayed in the admin panel
salt | The salt is random data used to decrypt your password hash. Create a different, long-enough (64 characters) salt for each user [here](http://www.sethcardoza.com/tools/random-password-generator/).
hash | The hash is a derivation of data used to authenticate. It represents your password in a way, nobody can read it. To create the hash you can use [this service](http://www.xorbin.com/tools/sha256-hash-calculator). Enter your hash + password and save the generated hash value.


### Module management

#### Basic Module

    {
        "id": "contact",
        "name": "Contact",
        "single": true,
        "fields": [
            {
                "name": "Name",
                "id": "name",
                "placeholder": "",
                "type": "text"
            },
            {
                "name": "Message",
                "id": "message",
                "placeholder": "",
                "type": "textarea"
            }
        ]
    }


#### Custom icons

You can change the icon displayed in the sidemenu of each module. Just specify an `icon` property for the module. You can use the famous [Entypo Icons](http://gumbyframework.com/docs/ui-kit/#!/icons).

    {
        "icon": "icon-cloud",
        ...
    }

### Field types

#### Text

    {
        "name": "Name",
        "id": "name",
        "placeholder": "",
        "type": "text"
    }

#### E-Mail

    {
        "name": "E-Mail",
        "id": "email",
        "placeholder": "",
        "type": "email"
    }

#### Textarea

    {
        "name": "Message",
        "id": "message",
        "placeholder": "",
        "type": "textarea"
    }

#### Select

    {
        "name": "Rating",
        "id": "rating",
        "placeholder": "- Choose -",
        "type": "select",
        "values": [
            {
                "label": "Ok",
                "value": 1
            },
            {
                "label": "Good",
                "value": 2
            },
            {
                "label": "Perfect",
                "value": 3
            }
        ]
    }


#### Image

    {
        "name": "Image",
        "id": "image",
        "placeholder": "",
        "type": "image"
    }

#### Youtube video

    {
        "name": "Video",
        "id": "video",
        "placeholder": "http://www.youtube.com/watch?v=xxx",
        "type": "youtube"
    }

### Field validation

#### Required fields
TBD

    {
        "required": true,
        ...
    }

#### Character limit
TBD

    {
        "limit": 255,
        ...
    }


Consuming data
--------------

### Create a basic view
To actually use the data we enter through the admin panel you have to create a view. Just create a <url>.json file in the `data/views` folder (e.g. videos.json) with the following content:

    {
        "module": "videos",
        "fields": [
            "video"
        ]
    }

Key | Value
--- | ---
module | The target module identifier (id) specified in  `config/modules.json`
fields | An array of fields of the given module, you want to return

That's it. Enter some data then simply access your data like this:
`http://yourhost/videos`

### Create a detail view
TBD



Roadmap
-------
TBD

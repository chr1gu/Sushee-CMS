Sushee CMS
==========

A JSON based content management system. **Work in progress!**

> This whole thing is just a first prototype. Don't use it, things might change a lot and there will be a heavy
> cleanup iteration soon! There will be a roadmap soon.

![alt text](https://raw.githubusercontent.com/chrigu-ebert/Sushee-CMS/master/web/admin/facebook.png "Logo")

About
-----
Sushee CMS has a few key features and goals.

Having a backend that is **simple** to setup and maintain is mostly the reason why I created this project. Almost all famous solutions out there have several dependencies and imply regular updates which is time-consuming. I'm not going into details here but all those updates have several annoying drawbacks.

I want to **customize** my backend to fit my client's needs. Naming conventions and data changes all the time and this needs to be a no-brainer.

**Performance** is very important because especially on mobile phones you have a limited bandwidth. Most of the existing solutions load plenty of vendor shit that makes your backend slow. Actually often you only need a fraction of all the things that are loaded. I directly read/write to the filesystem. No Database. Everything is loaded asynchronously with Javascript.



Setup
-----
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

Roadmap
-------
TBD

# Deserialisation-Demo

This is a demo of a simple PHP deserialisation vulnerability for [Sheffield Ethical Student Hackers](https://shefesh.com).

DISCLAIMER: We are an *ethical* hacking society, and this is for educational purposes only. Don't use it against a system unless you have explicit permission (this means don't use it against your demonstrator, too!) You can read our Code of Conduct [here](https://shefesh.com/downloads/SESH%20Code%20of%20Conduct.pdf).

## unsafe.php

This represents a simple web application that constructs a `SignupForm` object and writes to a file when the `__destruct` method is called. It accepts the GET parameter `username`, which it uses to write a username to a list of users who have signed up. It does this using the class' `parse_username` and `__destruct` functions.

## The Vulnerability

The class variables `$outfile` and `$username_string` are normally set by the `parse_username()` method. However, when a specially crafted `SignupForm` object with these variables already set is passed to the `username` GET parameter, it is deserialised and the `__destruct` method is automatically called. This skips the `parse_username()` method and overrides the variables, meaning their attacker-controlled values are used in the `file_put_contents` call.

The vulnerability only exists because untrusted data (i.e. user input) is passed directly to the `unserialize()` function. Remove this line, and the vulnerability is fixed. In fact, it's not even needed to make this program work - it is there just to teach about the mechanics of the vulnerability, but there may be situations where `unserialize()` may have a genuine use case (for example, if objects were seralized for more efficient storage). In this case, ensure that no untrusted data is allowed into the source of this deserialisation.

## generate-object.php

This is a recreation of the class being used on the web application, as could be created by an attacker with a 'whitebox' view of the web app's `unsafe.php` file.

It creates an instance of the class, with preset values that are used when the object is deserialised. The `$username_string` variable determines the contents that will be written to the file, and the `$outfile` variable determines the path (within the web server's directory). Change these to change the behaviour of your payload.

By default, the object looks like this:

`O:10:"SignupForm":2:{s:7:"outfile";s:10:"gotcha.txt";s:15:"username_string";s:25:"malicious stuff goes here";}`

The syntax is as follows:
- `O` tells us the type is an 'object'
- `10` tells us the character length of the class name `SignupForm`
- `2` tells us the number of variables within the object
- Within the curly brackets `{ }` are the object variables. They follow a similar structure, with each one having a type (`s` meaning 'string'), a variable name and length (`7:"outfile"`), and a value (a string (`s`), `10` characters long, called `"gotcha.txt"`). The variable names and values, and each pair thereof, are separated by semicolons `;`.

This will write the string "malicious stuff goes here" to the `gotcha.txt` file. Pretty harmless, but it should show you how the exploit works!

You can, of course, create these manually - but counting the length of a load of variable names is a pain and it's easy to make a syntax mistake!

## Triggering the Attack

A serialised object must be generated (see `generate-object.php`) and then passed to the `username` GET parameter. To test this locally, run your webserver:

`php -S localhost:5000`

Change your payload by editing the `$username_string` and `$outfile` variables in `generate-object.php`, then make a request to get your object:

`curl localhost:5000/generate-object.php`

Then make a request to the page, passing your object to the `username` GET parameter:

`localhost:5000/unsafe.php?username={SERIALISED OBJECT HERE}`

*Note:* If you use curl, bash may throw a fit at your curly brackets. It's easier to visit the URL in your browser instead

For example:

`localhost:5000/unsafe.php?username=O:10:"SignupForm":2:{s:7:"outfile";s:10:"gotcha.txt";s:15:"username_string";s:25:"malicious stuff goes here";}`

The `gotcha.txt` file should now appear in the webserver directory!

## Exploit Payloads

Our example exploit writes something fairly harmless to `gotcha.txt`, demonstrating the vulnerability. However, there are lots of ways to exploit arbitrary file write, even within the constraints of the current directory. Here are a couple of examples:

### Write a Web Shell

This won't necessarily allow you to escalate priveleges, but it lets you perform more actions on the box besides writing to files. You will have the permissions of the user who is running the web server (don't run your web servers as root!)

### Write an SSH key

The file location is limited by the `__DIR__` prefix. However, if for some ungodly reason this webserver was hosted in a user's home directory, a payload such as the following could write an SSH key to the `.ssh/authorized_keys` file. Like the above, this doesn't escalate your priveleges but allows you a more interactive shell

## Acknowledgements

Thanks to a recent Hack the Box for the inspiration... I won't say which, as it's live! But now you know how this works, go out and find it and root it...

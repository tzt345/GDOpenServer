## GDOpenServer
Basically a "server emulator" for Geometry Dash, except it's rewritten soon.  
Coming with new features, tools, configurations and literally no need of knowledge on how to use PHPMyAdmin, all documentated and easy to use!  
[![CodeFactor](https://www.codefactor.io/repository/github/jecket22/gdopenserver/badge/master)](https://www.codefactor.io/repository/github/jecket22/gdopenserver/overview/master)

# EY, LISTEN UP!
There haven't been made lots of tests so far, and it's currently still under heavy development.  
Most features are **NOT** working, even if there's a config provided. So please don't expect to think everything is working out of the box.  
I advise you to not use this for a public server until a release will be pushed.
Currently, tests are done on a Linux server, with MariaDB and PHP 7.4 installed.

### Setup (extremely simplified)
1. Upload all files to your webserver
2. Import the database into an existing MySQL/MariaDB database
3. Configurate files in /config (at least connection.php or your GDPS will completely fail)
4. Edit the links in the application (some links are encoded in Base64 since update 2.1)
- Requirements: Currently unknown.

### Credits
Most said code changes are already credited in https://github.com/Cvolton/GMDprivateServer.  
Big thanks to Intelligent-Cat and Wyliemaster for helping me working on this project.  
Additional credits to Alex1304 and DonAlex0 for some code they made that's included in here,  
also thanks to erfg12 for the Newgrounds-Scraper code and PHPMailer org for the PHPMailer binaries.

### Discord
If you need support, want to be updated or just want to chat, then you might want to join our Discord. https://discord.gg/PjFXRf5

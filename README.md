# EY, LISTEN UP!
**Seriously, read this before crying out loud for getting a bunch of errors.**  
There haven't been made lots of tests so far, and it's currently still under heavy development.  
Most features are **NOT** working, even if there's a config provided. So please don't expect to think everything is working out of the box.  
I advise you to not use this for a public server until a release will be pushed.
Currently, tests are done on a Linux server, with MariaDB and PHP 7.4 installed.

## GDOpenServer
Basically a soon-to-be-rewritten version of Cvolton's GMDPrivateServer, made to be compatible with many (if even bad) webhosts and be packed with a lot of features, especially made easy to understand (with documentation in mind) and less of a hassle to manage.

### Setup (extremely simplified)
1. Upload all files to your webserver
2. Import the database into an existing MySQL/MariaDB database
3. Configurate files in /config (at least connection.php or your GDPS will completely fail)
4. Edit the links in the application (some links are encoded in Base64 since update 2.1)
- Requirements: PHP +5.4, currently unknown if it's guaranteed to work on that specific version

### Credits
Most said code changes are already credited in https://github.com/Cvolton/GMDprivateServer.  
Big thanks to Intelligent-Cat and Wyliemaster for helping me working on this project.  
Additional credits to Alex1304, Absolute and DonAlex0 for letting us use their code/ideas for this project,  
also thanks to erfg12 for the Newgrounds-Scraper code, spyc for their yaml parser and PHPMailer org for the PHPMailer binaries.

### Discord
If you need support, want to be updated or just want to chat, then you might want to join our Discord. https://discord.gg/PjFXRf5

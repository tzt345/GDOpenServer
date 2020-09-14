# Commands
### Basics
Almost all GD Private Servers come with a handy in-game tool you can use by commenting on a level or profile.  
Commenting such "commands" will send the command to the server to do something.  
As an example, commenting **!unrate** on a level will unrate the level _completely_, setting the level to have no difficulty rating, no stars and no coins.

Additionally, commands can have "parameters", meaning that they can/should have options added after the command.  
Another example, **!rate insane 9 1** will rate the level with the insane difficulty, 9 stars and be featured.  
You can probably guess where it goes for other commands.

Note when commenting a command: You WILL recieve a "Comment Upload failed!" message. This is intentional and just tells you that you did everything correct.

### Configuration
You must create a new "role" using the **Role Creation Tool** in the Dashboard.  
Depending on which commands you need, you should also add the permission for the command to the role.

## List of commands (sorted (soon) by alphabetical order)
A command is formatted like this: ``!command [Required Input] <Optional Input>``  
- `!rate [difficulty name] [amount of stars, 0-10] <featured? 0/1> <epic? 0/1> <verify coins? 0/1>`  
This command rates the level you're commenting on.  
Requires follwing permission: ``commandRate`` (Optional: ``commandFeature``, ``commandEpic``, ``commandVerifycoins``)
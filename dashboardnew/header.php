<!-- this should be edited with php later on, I only made this so I don't have to worry about making a navbar in the future -->
<style>
body {margin:0;font-family:Verdana;color:white;background:url("https://media.discordapp.net/attachments/756123944502820894/816289742408253460/gitmesh.png")}
.content {margin:1% 10%;word-wrap: break-word}

ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  overflow: hidden;
  background-color: #3d5c4d;
}

li {
  float: left;
}

li a, .dropbtn {
  display: inline-block;
  color: white;
  text-align: center;
  padding: 14px 16px;
  text-decoration: none;
}

li a:hover, .dropdown:hover .dropbtn {
  background-color: #57db99;
}

li.dropdown {
  display: inline-block;
}

.dropdown-content {
  display: none;
  position: absolute;
  min-width: 160px;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 1;
}

.dropdown-content a {
  color: black;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
  text-align: left;
  background-color: white;
}

.dropdown-content a:hover {background-color: #f1f1f1;}

.dropdown:hover .dropdown-content {
  display: block;
}
</style>
<body>
<ul>
  <li><a href="#home">Home</a></li>
  <li><a href="#download">Download</a></li>
  <li><a href="#announce">Announcements</a></li>
  <li class="dropdown">
    <a href="javascript:void(0)" class="dropbtn">Statistics</a>
    <div class="dropdown-content">
      <a href="#levelstats">Levels</a>
      <a href="#songlist">Songs</a>
      <a href="#leaderboard">Leaderboard</a>
      <a href="#packs">Packs/Gauntlets</a>
      <a href="#modactions">Mod Actions</a> <!-- 1 -->
    </div>
  </li>
  <li class="dropdown">
    <a href="javascript:void(0)" class="dropbtn">Tools</a>
    <div class="dropdown-content">
      <a href="#accman">Account Management</a>
      <a href="#levelman">Level Management</a>
      <a href="#profile">View Profile</a>
      <a href="#reup">Reupload Level/Songs</a>
      <a href="#modtools">Moderation Tools</a> <!-- 1 -->
      <a href="#adminpan">Admin Panel</a> <!-- 2 -->
    </div>
  </li>
  <!-- should be displayed if you haven't logged in -->
  <li style="float:right"><a href="#login">Login</a></li>
  <li style="float:right"><a href="#reg">Register</a></li>
  <!-- otherwise show this (with the right info ofc) -->
  <li style="float:right"><a href="#accman">Welcome you dumb idiot~</a></li>
</ul>
</body>
<div class="content">
haha xml ordering go brrr
</div>

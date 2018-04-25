# INSTALLATION
1. If you want use MySQL as main database server, create and upload "dump.sql" to your MySQL database.
2. If you want use MySQL as main database server, open file "/config/_core.php", set "dbDriver" to "mysql" and set attributes "mysqlHost", "mysqlUser", "mysqlPassword", "mysqlDatabase" on your own.
3. If you want use SQLite as main database server, open file "/config/_core.php" and set "dbDriver" to "sqlite".
4. Upload "/www/*" to document_root your server.
5. Verify filepermisions (scripts must have "write" rights) to folders "/admin/cache", "/admin/config", "/admin/data", "/cache", "/config", "/data", "/public", "/tmp".
6. Use link http://YOUR_HOST/user/login to authorize as administrator (login: root, password: masterkey).

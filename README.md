Todo List Manager
=====

# Installation

`git clone https://github.com/christopherfouquier/todo.git`

Take care about `RewriteBase` in your `.htaccess` and change it depending of your installation folder.

Install and run composer

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar install
    $ propel-gen main && propel-gen insert-sql
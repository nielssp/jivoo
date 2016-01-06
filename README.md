# Jivoo – PHP web application framework

[![Build Status](https://travis-ci.org/jivoo/jivoo.svg?branch=master)](https://travis-ci.org/jivoo/jivoo) [![Coverage Status](https://coveralls.io/repos/jivoo/jivoo/badge.svg?branch=master&service=github)](https://coveralls.io/github/jivoo/jivoo?branch=master)

Jivoo is an experimental web application framework for PHP.

Although the framework is usable at the moment (currently used on [agendl.com](http://agendl.com), [nielssp.dk](http://nielssp.dk), and [apakoh.dk](http://apakoh.dk)), I do not recommend using it for anything important.

Some examples of simple applications are available in the examples directory.

## Goals

1. Portable: Applications should work on most web servers running at least PHP 5.3 using one of the supported database systems (MySQL, SQLite and PostgreSQL for the time being).
2. Fast prototyping: Built-in development tools for generating and configuring applications.
3. Robust: Support for database migrations, installation/updating, and error handling.

## Features

* Routing system
* Model–View–Controller
* Active records
* Database abstraction
* Database migration
* Template system
* Installation and update system
* Extension system
* Command line interface
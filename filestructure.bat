@echo off
REM This batch file creates the directory and file structure for the simple POS system.

REM Define the root directory name
set ROOT_DIR=pos_system

REM Create the root directory
echo Creating root directory: %ROOT_DIR%
mkdir %ROOT_DIR%

REM Create subdirectories
echo Creating subdirectories...
mkdir %ROOT_DIR%\data
mkdir %ROOT_DIR%\includes
mkdir %ROOT_DIR%\assets
mkdir %ROOT_DIR%\assets\css
mkdir %ROOT_DIR%\assets\js

REM Create main PHP files
echo Creating main PHP files...
type nul > %ROOT_DIR%\index.php
type nul > %ROOT_DIR%\products.php
type nul > %ROOT_DIR%\transactions.php
type nul > %ROOT_DIR%\reports.php

REM Create JSON data files
echo Creating JSON data files...
type nul > %ROOT_DIR%\data\products.json
type nul > %ROOT_DIR%\data\transactions.json

REM Create include files
echo Creating include files...
type nul > %ROOT_DIR%\includes\header.php
type nul > %ROOT_DIR%\includes\footer.php
type nul > %ROOT_DIR%\includes\functions.php

REM Create asset files (minimal CSS and JS placeholders)
echo Creating asset files...
type nul > %ROOT_DIR%\assets\css\style.css
type nul > %ROOT_DIR%\assets\js\script.js

echo Folder and file structure created successfully in .\%ROOT_DIR%\
pause

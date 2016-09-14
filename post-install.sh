#!/bin/sh

echo "File listings:"
ls -l

echo ""
echo "PHPCompatibility Directory:"
ls ./PHPCompatibility -l

echo ""
echo "Sniffs Directory:"
ls ./Sniffs -l

#echo ""
#echo "Clearing old install"
#rm -rf ./PHPCompatibility/*
#mkdir -p ./PHPCompatibility/Sniffs

#echo ""
#echo "Moving new files"
#mv -f Sniff.php ./PHPCompatibility/Sniff.php
#mv -f Sniffs/* ./PHPCompatibility/Sniffs/*

echo ""
echo "Setting PHPCS installed_paths"
"vendor/bin/phpcs" --config-set installed_paths ../../..
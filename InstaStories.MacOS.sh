#!/bin/bash
cd "$( dirname "${0}" )"
cd bin/
php InstaStories.php ../ $1 $2 $3

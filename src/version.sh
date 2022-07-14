#! /bin/bash
ctag=$(git tag  | grep -E '^v[0-9]' | sort -V | tail -1)
cdate=$(git log -1 --format=%cd --date=format:'%Y-%m-%d %H:%M %z');
echo $GIT_WORKING_DIR;
echo -ne  "Vers:  $ctag $cdate \n" > $GIT_DIR/data/version;

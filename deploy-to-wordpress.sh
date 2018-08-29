#!/bin/bash
set -e

# get parameters
POSITIONAL=()
while [[ $# -gt 0 ]]
do
key="$1"

case $key in
    --svn)
    SVN=YES
    SVN_URL="$2"
    shift # past argument
    shift # past value
    ;;
    *) # unknown option
    POSITIONAL+=("$1") # save it in an array for later
    shift # past argument
    ;;
esac
done
set -- "${POSITIONAL[@]}" # restore positional parameters

if [ "$SVN" = YES ]; then
    TAG=$(git describe --tags)
    # remove first character (usually a 'v')
    #TAG=${TAG:1:${#TAG}}

    mkdir svn
    svn co $SVN_URL svn
    rsync -rv --delete \
        --exclude '*.pot~' \
        --exclude '*.po~' \
        --exclude 'lib/test' \
        --exclude 'lib/obsolete' \
        --exclude 'composer.json' \
        --exclude 'composer.lock' \
        --exclude 'nbproject' \
        --exclude '.git' \
        --exclude '.gitignore' \
         src/woocommerce-gateway-ratenkaufbyeasycredit/* svn/trunk/ 
    cd svn
    svn stat
    cd trunk

    # see https://stackoverflow.com/a/20095520/3461955
    svn st | grep '^\?' | sed 's/^\? *//' | xargs -I% svn add %
    cd ..
    svn ci -m "Version ${TAG}"
    svn cp trunk tags/${TAG}
    svn ci -m "Tag ${TAG}"
fi

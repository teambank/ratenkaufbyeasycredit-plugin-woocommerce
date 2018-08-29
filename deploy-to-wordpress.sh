#!/bin/bash

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

version_statement=$(grep "define( 'WC_RATENKAUFBYEASYCREDIT_VERSION" src/woocommerce-gateway-ratenkaufbyeasycredit/woocommerce-gateway-ratenkaufbyeasycredit.php)
PLUGINVERSION=$(php -r "$version_statement echo WC_RATENKAUFBYEASYCREDIT_VERSION;")

if git show-ref --tags --quiet --verify -- "refs/tags/$PLUGINVERSION"
    then
        echo "Git tag $PLUGINVERSION does exist. Let's continue..."
    else
        echo "$PLUGINVERSION does not exist as a git tag. Aborting.";
        exit 1;
fi

if [ "$SVN" = YES ]; then
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
    svn ci --non-interactive --username $WORDPRESS_USER --password $WORDPRESS_PW -m "Version ${PLUGINVERSION}"
    svn cp trunk tags/${PLUGINVERSION}
    svn ci --non-interactive --username $WORDPRESS_USER --password $WORDPRESS_PW -m "Tag ${PLUGINVERSION}"
fi

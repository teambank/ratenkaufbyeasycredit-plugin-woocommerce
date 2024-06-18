#!/bin/bash
set -e 

DIR="$(cd "$(dirname "$0")"; pwd)";
cd $DIR/..

source .env


if [ -z "$SVN_URL" ]; then
  echo "repository url is empty"; exit;
fi;

echo "pushing to $SVN_URL"

version_statement=$(grep "define('WC_RATENKAUFBYEASYCREDIT_VERSION" src/woocommerce-gateway-ratenkaufbyeasycredit/woocommerce-gateway-ratenkaufbyeasycredit.php)
PLUGINVERSION=$(php -r "$version_statement echo WC_RATENKAUFBYEASYCREDIT_VERSION;")

if git show-ref --tags --quiet --verify -- "refs/tags/$PLUGINVERSION"
    then
        echo "Git tag $PLUGINVERSION does exist. Let's continue..."
    else
        echo "$PLUGINVERSION does not exist as a git tag. Aborting.";
        exit 1;
fi

[ -d ./svn ] || mkdir ./svn 
[ -d ./svn ] && rm -rf ./svn

echo "svn checkout $SVN_URL svn"
svn checkout $SVN_URL svn
rsync -rv --delete \
	--exclude '*.pot~' \
	--exclude '*.po~' \
	--exclude 'composer.json' \
	--exclude 'composer.lock' \
	--exclude 'nbproject' \
	--exclude '.git' \
	--exclude '.gitignore' \
	--exclude '.vscode' \
    --exclude 'node_modules' \
	 src/woocommerce-gateway-ratenkaufbyeasycredit/* svn/trunk/
rsync -rv --delete assets/* svn/assets/

cd svn
svn stat
cd trunk
# see add all unversioned but not ignored files, https://stackoverflow.com/a/20095520/3461955
svn st | grep '^\?' | sed 's/^\? *//' | xargs -I% svn add %
svn st | grep ^! | awk '{print " --force "$2}' | xargs svn rm
cd ..

svn commit --non-interactive --username $WORDPRESS_USER --password $WORDPRESS_PW -m "Version ${PLUGINVERSION}"
svn mkdir tags/${PLUGINVERSION}
svn cp trunk/* tags/${PLUGINVERSION}/
svn commit --non-interactive --username $WORDPRESS_USER --password $WORDPRESS_PW -m "Tag ${PLUGINVERSION}"

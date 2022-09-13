DIR="$(cd "$(dirname "$0")"; pwd)";
cd $DIR/..

[ -d ./build ] || mkdir ./build
[ -d ./dist ] || mkdir ./dist
rm -r build/*

composer install --no-dev

rsync -rv \
  --exclude '*backup*' \
  --exclude 'test' \
  --exclude 'obsolete' \
  --exclude '.gitignore' \
  --exclude '.vscode' \
  --exclude '.git' \
  --exclude 'merchant-interface' \
 src/* build/

version_statement=$(grep "define( 'WC_RATENKAUFBYEASYCREDIT_VERSION" src/woocommerce-gateway-ratenkaufbyeasycredit/woocommerce-gateway-ratenkaufbyeasycredit.php)
version=$(php -r "$version_statement echo WC_RATENKAUFBYEASYCREDIT_VERSION;")
echo $version
rm dist/wc-easycredit-$version.zip
(cd build && zip -r - *) > dist/wc-easycredit-$version.zip

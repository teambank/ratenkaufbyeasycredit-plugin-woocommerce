DIR="$(cd "$(dirname "$0")"; pwd)";
cd $DIR/..

mkdir build
mkdir dist
rm -r build/*

composer2 install --no-dev

rsync -rv \
  --exclude '*backup*' \
  --exclude 'test' \
  --exclude 'obsolete' \
  --exclude '.gitignore' \
  --exclude '.vscode' \
  --exclude '.git' \
  --exclude 'merchant-interface' \
 src/* build/
rm -r build/woocommerce-gateway-ratenkaufbyeasycredit/lib/test

version_statement=$(grep "define( 'WC_RATENKAUFBYEASYCREDIT_VERSION" src/woocommerce-gateway-ratenkaufbyeasycredit/woocommerce-gateway-ratenkaufbyeasycredit.php)
version=$(php -r "$version_statement echo WC_RATENKAUFBYEASYCREDIT_VERSION;")
echo $version
rm dist/wc-easycredit-$version.zip
(cd build && zip -r - *) > dist/wc-easycredit-$version.zip

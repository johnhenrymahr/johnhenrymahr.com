#! /bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
TESTDIR="$(cd "${DIR}/../tests" && pwd)"
PHPUNITPATH="$(cd "${DIR}/../vendor/bin/" && pwd)"
VENDOR="$(cd "${DIR}/../vendor/" && pwd)"
echo ""
echo "<------------------(Test Start)------------------>"
echo ''
echo "base directory: ${DIR}"
echo "phpunit directory: ${PHPUNITPATH}"
echo $"autoload path: ${VENDOR}/autoload.php"

if [ ! -d "${PHPUNITPATH}" ]; then
  echo "phpunit directory not found. Run composer install first."
  exit -1
fi
if [ ! -e "${PHPUNITPATH}/phpunit" ]; then
  echo "phpunit executable not found. Re-run composer install."
  exit -1
fi
if [ ! -e "${VENDOR}/autoload.php" ]; then
  echo "php autoload file not found. Re-run composer dump-autoload."
  exit -1
fi
if [ ! -d "${TESTDIR}" ]; then
  echo "Test directory not found."
  exit -1
fi
echo "open up permissions directory on data directory"
chmod 777 "${TESTDIR}/data"
echo "Executing tests in ${TESTDIR}"
${PHPUNITPATH}/phpunit --bootstrap ${VENDOR}/autoload.php ${TESTDIR}
echo ''
echo "<------------------(Test End)------------------>"
exit 1

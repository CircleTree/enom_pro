#!/bin/bash


function escape_chars {
    sed -E 's/(\{\}")/\\\1/g'
}
function format {
    sha=$(git log -n 1 --pretty=format:%h $1 | escape_chars)
    message=$(git log -n 1 --pretty=format:%B $1 | escape_chars)
    author=$(git log -n 1 --pretty=format:'%aN <%aE>' $1 | escape_chars)
    commit=$(git log -n 1 --pretty=format:%cE $1 | escape_chars)
    date=$(git log -n 1 --pretty=format:%cD $1 | escape_chars)
    echo "{\"sha\":\"$sha\",\"message\":\"$message\",\"author\":\"$author\",\"commit\":\"$commit\",\"date\":\"$date\"}"
}

echo '['
for hash in $(git rev-list HEAD --max-count=15)
do
  format $hash
done
echo ']'

mkdir pialab
cd pialab
git clone ssh://git@git.pialab.io:2222/pialab/front.git --single-branch --depth 1
git clone ssh://git@git.pialab.io:2222/pialab/back.git --single-branch --depth 1
docker build . -t pialab


docker run -p 8000:80 -t \
  --name pialab \
  --hostname pialab \
  --mount source=pialab_db,target=/var/lib/postgresql \
  pialab



docker build . -t pialab-back:1644
docker run -p 8000:80 -d -t --name pialab-test --hostname pialab-test --restart always pialab-back:1644
http://localhost:8000/index.php


docker build . -t pialab-front:1957
docker run -p 4200:4200 -d -t --name pialab-testfront --hostname pialab-testfront --restart always pialab-front:1957
http://localhost:4200/

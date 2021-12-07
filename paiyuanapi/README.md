lnmp 安装 
下个安装包
wget http://soft.vpser.net/lnmp/lnmp1.6.tar.gz -cO lnmp1.6.tar.gz && tar zxf lnmp1.6.tar.gz && cd lnmp1.6 && ./install.sh lnmp
如需要安装LNMPA或LAMP，将./install.sh 后面的参数lnmp替换为lnmpa或lamp即可
官网地址
https://lnmp.org/install.html

node
下个安装包上传到linux
解压 tar xf node-v8.3.0-linux-x64.tar.xz
cd node-v8.3.0-linux-x64
设置软连接
ln -s /home/wwwroot/node/bin/node /usr/local/bin/node
ln -s /home/wwwroot/node/bin/npm /usr/local/bin/npm
node -v
npm -v

pm2
npm install -g pm2
ln -s NodeJS的目录/bin/pm2 /usr/local/bin/pm2

暂停更新公告图片
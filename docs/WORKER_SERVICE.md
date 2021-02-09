# CakePHP Queue Plugin - Worker Service


## Steps to create a worker service

### Create file service
You may create a file called `app_default_queue_worker.service` inside the folder `/etc/systemd/system/`.

Then set the following values:

```
[Unit]
Description=Default Queue Worker Processor
After=multi-user.target

[Service]
Type=simple
ExecStart=/path_to_app/bin/cake QueueWorker run -q
Restart=always

[Install]
WantedBy=multi-user.target
```

### Now enable and start this service

```
$ cd /etc/systemd/system
$ sudo systemctl enable app_queue_default
$ sudo systemctl start app_queue_default // to start
$ sudo systemctl stop app_queue_default // to stop
```

### Also, make sure that our cake shell is executable. If not, make it via

```
chmod u+x -R [path_to_app]/bin
```
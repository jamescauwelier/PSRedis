# Integration testing

To run the integration tests, make sure you have the following set up:

- sentinel on a VM running on IP 192.168.50.40 and port 26379
- sentinel on a VM running on IP 192.168.50.41 and port 26379
- sentinel on a VM running on IP 192.168.50.30 and port 26379
- redis master on a VM running on IP 192.168.50.40 and port 6379
- redis slave on a VM running on IP 192.168.50.41 and port 6379
- the master name in the sentinels configuration is set to 'mymaster'

In the future this will be cleaned up and be more automatic, but for now I am manually installing redis and sentinel
on local VM's using Vagrant and VirtualBox.  Pull requests with more automatic provision in this would be helpful.

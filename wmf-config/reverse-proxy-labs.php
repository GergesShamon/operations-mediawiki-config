<?php
# WARNING: This file is publicly viewable on the web. Do not put private data here.

# our text CDN in beta labs gets forwarded requests
# from the ssl terminator, to 127.0.0.1, so adding that
# address to the NoPurge list so that XFF headers for it
# will be stripped; purge requests should get to it
# on the address in the CdnServers list

$wgCdnServersNoPurge = [
	'127.0.0.1',
	// deployment-cache-text08
	'172.16.3.164',
	// deployment-cache-upload08
	'172.16.3.146',
];

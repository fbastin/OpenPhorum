FILES="include/api/file.php,include/api/custom_profile_fields.php,include/api/base.php,include/api/user.php,include/api/modules.php,include/api/write_file.php,include/api/http_get.php,include/api/image.php"

all:
	(cd ../../..; phpdoc \
	       -f $(FILES) \
	       -t docs/html/api \
	       -ti "OpenPhorum API Documentation" \
	       -o HTML:frames:phphtmllib -dn OpenPhorumAPI)
	cp index.html.custom index.html

clean:
	/bin/rm -f *.html
	/bin/rm -fR media
	/bin/rm -fR docs
	/bin/rm -fR OpenPhorumAPI
	/bin/rm -fR __examplesource


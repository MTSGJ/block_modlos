<?php

function xmldb_block_modlos_upgrade($oldversion=0)
{
	global $CFG, $THEME, $DB;

	$dbman = $DB->get_manager();


	// 2010083024
	if ($oldversion < 2010083024) {
		$table = new xmldb_table('modlos_mute_list');

		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->add_field('agentid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('muteid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('mutename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('mutetype',  XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
		$table->add_field('muteflags', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
		$table->add_field('timestamp', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$dbman->create_table($table);
	}



	// 2010090100
	if ($oldversion < 2010090100) {
		$table = new xmldb_table('modlos_login_screen');

		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('information', XMLDB_TYPE_TEXT, 'big', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('bordercolor', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null, null, 'white');
		$table->add_field('timestamp', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

		$table->add_key('id', XMLDB_KEY_PRIMARY, array('id'));
		$dbman->create_table($table);
	}



	//  2010092200
	if ($oldversion < 2010092200) {
		$table = new xmldb_table('modlos_search_hostsregister');
 		if ($dbman->table_exists($table)) $dbman->drop_table($table);

		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->add_field('host', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('port', XMLDB_TYPE_INTEGER, '5', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('register', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('nextcheck', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('checked', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('failcounter', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('host', XMLDB_KEY_UNIQUE, array('host', 'port'));
		$dbman->create_table($table);
	}


	if ($oldversion < 2010092200) {
		$table = new xmldb_table('modlos_search_allparcels');
 		if ($dbman->table_exists($table)) $dbman->drop_table($table);

		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->add_field('regionuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('parcelname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('owneruuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, '00000000-0000-0000-0000-000000000000');
		$table->add_field('groupuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, '00000000-0000-0000-0000-000000000000');
		$table->add_field('landingpoint', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('parceluuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, '00000000-0000-0000-0000-000000000000');
		$table->add_field('infouuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, '00000000-0000-0000-0000-000000000000');
		$table->add_field('parcelarea', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('parceluuid', XMLDB_KEY_UNIQUE, array('parceluuid'));
		$dbman->create_table($table);
	}


	if ($oldversion < 2010092200) {
		$table = new xmldb_table('modlos_search_parcels');
 		if ($dbman->table_exists($table)) $dbman->drop_table($table);

		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->add_field('regionuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('parcelname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('parceluuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('landingpoint', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('description', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('searchcategory', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('build',  XMLDB_TYPE_CHAR, '6', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('script', XMLDB_TYPE_CHAR, '6', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('public', XMLDB_TYPE_CHAR, '6', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('dwell', XMLDB_TYPE_NUMBER, '20, 8', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('infouuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('mature', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null, null, 'PG');

		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('uuid', XMLDB_KEY_UNIQUE, array('regionuuid', 'parceluuid'));
		$dbman->create_table($table);
	}


	if ($oldversion < 2010092200) {
		$table = new xmldb_table('modlos_search_parcelsales');
 		if ($dbman->table_exists($table)) $dbman->drop_table($table);

		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->add_field('regionuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('parcelname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('parceluuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('area', XMLDB_TYPE_INTEGER, '6', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('saleprice', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('landingpoint', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('infouuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, '00000000-0000-0000-0000-000000000000');
		$table->add_field('dwell', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('parentestate', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '1');
		$table->add_field('mature', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null, null, 'PG');

		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('uuid', XMLDB_KEY_UNIQUE, array('regionuuid', 'parceluuid'));
		$dbman->create_table($table);
	}


	if ($oldversion < 2010092200) {
		$table = new xmldb_table('modlos_search_events');
 		if ($dbman->table_exists($table)) $dbman->drop_table($table);

		$table->add_field('id', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->add_field('uid', XMLDB_TYPE_INTEGER, '8', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
		$table->add_field('owneruuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('eventid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('creatoruuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('category', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('description', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('dateutc', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('duration', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('covercharge', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('coveramount', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('simname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('globalpos', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('eventflags', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

		$table->add_key('id', XMLDB_KEY_PRIMARY, array('id'));
		$dbman->create_table($table);
	}


	if ($oldversion < 2010092200) {
		$table = new xmldb_table('modlos_search_regions');
 		if ($dbman->table_exists($table)) $dbman->drop_table($table);

		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->add_field('regionname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('regionuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('regionhandle', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('url', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('owner', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('owneruuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);

		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('regionuuid', XMLDB_KEY_UNIQUE, array('regionuuid'));
		$dbman->create_table($table);
	}


	if ($oldversion < 2010092200) {
		$table = new xmldb_table('modlos_search_popularplaces');
 		if ($dbman->table_exists($table)) $dbman->drop_table($table);

		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->add_field('regionuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('dwell', XMLDB_TYPE_NUMBER, '20, 8', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('infouuid', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('has_picture', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('mature', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, null);

		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$dbman->create_table($table);
	}


	if ($oldversion < 2010092200) {
		$table = new xmldb_table('modlos_search_objects');
 		if ($dbman->table_exists($table)) $dbman->drop_table($table);

		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->add_field('objectuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('parceluuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('location', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('description', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('regionuuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null, null, null);

		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('uuid', XMLDB_KEY_UNIQUE, array('objectuuid', 'parceluuid'));
		$dbman->create_table($table);
	}


	if ($oldversion < 2016053002) {
		$table = new xmldb_table('modlos_template_avatars');
 		if ($dbman->table_exists($table)) $dbman->drop_table($table);

		$table->add_field('id',       XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
		$table->add_field('num',      XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('title',    XMLDB_TYPE_CHAR,   '128', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('uuid',     XMLDB_TYPE_CHAR,    '36', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('text',     XMLDB_TYPE_TEXT,   'big', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('format',   XMLDB_TYPE_INTEGER,  '4', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('fileid',   XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('filename', XMLDB_TYPE_CHAR,   '128', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('itemid',   XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, null);
		$table->add_field('timestamp',XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$dbman->create_table($table);
	}

	if ($oldversion < 2016053002) {
		$table = new xmldb_table('modlos_codetable');
 		if ($dbman->table_exists($table)) $dbman->drop_table($table);
	}

	if ($oldversion < 2016053002) {
		$table = new xmldb_table('modlos_economy_money');
 		if ($dbman->table_exists($table)) $dbman->drop_table($table);
	}

	if ($oldversion < 2016053002) {
		$table = new xmldb_table('modlos_economy_transactions');
 		if ($dbman->table_exists($table)) $dbman->drop_table($table);
	}


	if ($oldversion < 2016071700) {
		$table = new xmldb_table('modlos_template_avatars');
        //
        $field = new xmldb_field('status',  XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'itemid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
		$key = new xmldb_key('uuid', XMLDB_KEY_UNIQUE, array('uuid'));
        $dbman->add_key($table, $key);
	}

	if ($oldversion < 2016071700) {
		$table = new xmldb_table('modlos_mute_list');
		$key = new xmldb_key('muteid', XMLDB_KEY_UNIQUE, array('agentid, muteid, mutename'));
        $dbman->add_key($table, $key);
	}

	if ($oldversion < 2016071700) {
		$table = new xmldb_table('modlos_search_allparcels');
        $index = new xmldb_index('regionuuid', XMLDB_INDEX_NOTUNIQUE, array('regionuuid'));
        $dbman->add_index($table, $index);
	}

	if ($oldversion < 2016071700) {
		$table = new xmldb_table('modlos_search_events');
		$key = new xmldb_key('eventid', XMLDB_KEY_UNIQUE, array('eventid'));
        $dbman->add_key($table, $key);
	}

	if ($oldversion < 2016071700) {
		$table = new xmldb_table('modlos_search_parcels');
        $index = new xmldb_index('name', XMLDB_INDEX_NOTUNIQUE, array('parcelname'));
        $dbman->add_index($table, $index);
        $index = new xmldb_index('description', XMLDB_INDEX_NOTUNIQUE, array('description'));
        $dbman->add_index($table, $index);
        $index = new xmldb_index('searchcategory', XMLDB_INDEX_NOTUNIQUE, array('searchcategory'));
        $dbman->add_index($table, $index);
        $index = new xmldb_index('dwell', XMLDB_INDEX_NOTUNIQUE, array('dwell'));
        $dbman->add_index($table, $index);
	}


	if ($oldversion < 2016080902) {
		$table = new xmldb_table('modlos_profile_userpicks');
        //
        $field = new xmldb_field('gatekeeper',  XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '', 'enabled');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
	}


	if ($oldversion < 2016092800) {
		$table = new xmldb_table('modlos_users');
        $index = new xmldb_index('user_id', XMLDB_INDEX_NOTUNIQUE, array('user_id'));
        $dbman->add_index($table, $index);
	}

	return true;
}

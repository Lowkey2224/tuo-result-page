#!/bin/bash

trap times EXIT

function hilfe {
	echo "
	Aufruf: ./import_db.sh [OPTION]
	Liest Datenbanken von Zielsystemen ein, und in die Lokale DB ein.

	-P Pretend Liest die Daten nicht ein, und löscht auch nicht den SQL dump.
	-d DATENBANKNAME Übergibt einen Datenbank name, der lokal & entfernt benutzt wird.
	-h Ruft diese Hilfe auf.
	-u [USERNAME] Spezifiziert den lokalen Datenbank benutzer
	-k Keep Beduetet, dass der komprimierte SQL Dump nicht gelöscht wird
	-p gibt das PAsswort für die Lokale Datenbank an.
	-e One Insert for each row
	-c Each insert will have its keys"
	exit 0
}

sshAddr="root@dieser-loki.de"
scpAddr="root@dieser-loki.de"

dbUser="homestead"
dbPass="secret"
dbName="tuo_results"
dbNameLocal="tuo_results"
persist=true;
drop=true;
keep=false
verbosity=0



while getopts Ppd:hDu:kvc opt; do
  case $opt in
    D)
      echo "Database will not be dropped" >&2
      drop=false
      ;;
    P)
      echo "Data will not be read into local DB" >&2
      persist=false
      ;;
    c)
      complete=true
      ;;
    e)
      extended=true
      ;;
    k)
      keep=true
      ;;
    v)
      ((verbosity++))ll /tm
      ;;
    h)
      hilfe
      ;;
    u)
      dbUser=$OPTARG
      ;;
    p)
      dbPass=$OPTARG
      ;;
    d)
      dbNameLocal=$OPTARG
      dbName=$OPTARG
      ;;
    :)
      echo "Option -$OPTARG requires an argument." >&2
      exit 1
      ;;
    \?)
      echo "Invalid option: -$OPTARG" >&2
      ;;
  esac
done


echo "Spiele Datenbank von Zeitenmeldung ein"
#dbName="tuo_results"
#dbNameLocal="homestead"
dbUserRemote="tuo-results"
dbPassRemote="yxBc2DystzQN"



dbBackup="/tmp/${dbName}.sql.gz"

options=""
if [ "$extended" = "true" ]; then
options="$options --extended-insert=false"
fi
if [ "$complete" = "true" ]; then
options="$options --complete-insert"
fi

#set -x

echo -e "Creating Dump with Options \e[93m${options}\e[39m"
echo ssh ${sshAddr}  "mysqldump -u ${dbUserRemote} --password=${dbPassRemote} ${options} ${dbName} | gzip -9 >${dbBackup}"
ssh ${sshAddr}  "mysqldump -u ${dbUserRemote} --password=${dbPassRemote} ${options} ${dbName} | gzip -9 >${dbBackup}"




echo "Download Dump"
echo "scp ${scpAddr}:${dbBackup} ${dbBackup}"
scp ${scpAddr}:${dbBackup} ${dbBackup}

#echo "ssh ${sshAddr} \"rm -f ${dbBackup}\"\n"
echo "remove sqldump on remote server"
ssh ${sshAddr} "rm -f ${dbBackup}"

#echo "mysql -u root --password=${dbPass} -e \"DROP DATABASE ${dbName}; CREATE DATABASE ${dbName};\"\n"
echo "drop local tables"
if [ "$persist" = "true" ]; then
	if [ "$drop" = "true" ]; then
		mysql -u ${dbUser} --password=${dbPass} -e "DROP DATABASE ${dbNameLocal}"
		#if [ $? ne 0 ]; then
		#echo "Datenbank konnte nicht gelöscht werden"
		#fi
		mysql -u ${dbUser} --password=${dbPass} -e "CREATE DATABASE ${dbNameLocal};"
	else
		mysql -u ${dbUser} --password=${dbPass} -e "CREATE DATABASE ${dbNameLocal};"
	fi
	echo "insert dumpfile"
	gunzip < "${dbBackup}" | mysql -u ${dbUser} --password=${dbPass} "${dbNameLocal}"
	if [ "$keep" = "false" ]; then
	echo "Remove local dump file"
	rm -f "${dbBackup}"
	fi

else
	echo  "SQL Dump wurde nicht eingelesen oder gelöscht. Er ist hier: ${dbBackup}"
fi




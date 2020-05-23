import mysql.connector
from mysql.connector import Error
import credentials
import re
import hashlib
from datetime import datetime,timedelta,date

import mwclient
import login

masterwiki =  mwclient.Site('en.wikipedia.org')
masterwiki.login(login.username,login.password)

def callAPI(params):
    return masterwiki.api(**params)

def calldb(command,style):
    try:
        print command
        connection = mysql.connector.connect(host=credentials.ip,
                                             database=credentials.database,
                                             user=credentials.user,
                                             password=credentials.password)
        if connection.is_connected():
            cursor = connection.cursor()
            cursor.execute(command)
            if style == "read":
                record = cursor.fetchall()
            if style == "write":
                connection.commit()
    except Error as e:
        print("Error while connecting to MySQL", e)
    finally:
        if (connection.is_connected()):
            cursor.close()
            connection.close()
        if style == "read":return record
        else:return "Done"

def revokeReadPerms(userid):
    userid = str(userid)
    calldb("update permissions set user=0 where id = "+userid+" and wiki = 'enwiki';","write")

def checkAllPerms():
    result = calldb("select * from users where wikis = 'enwiki';","read")
    for user in result:
        id = user[0]
        username = str(user[1])
        params = {'action': 'query',
                'format': 'json',
                'list': 'users',
                'ususers': username,
                'usprop': 'groups'
                }
        raw = callAPI(params)
        try:
            results = raw["query"]["users"][0]["groups"]
            print results
            for result in results:
                print "I see sysop for: "+username
                if "sysop" in result:continue #no modification needed
            print "Going to revoke: "+username
            quit()
            revokeReadPerms(user[0])
        except:
            print "Going to revoke: "+username
            quit()
            revokeReadPerms(user[0])
        ###################################
        ###Set allowed Wikis###############
        if "enwiki" in user[6]:
            if "," in user[6]:
                wikis = user[6].split(',')
                rebuildwikis = []
                for wiki in wikis:
                    if wiki=="enwiki":continue
                    else:
                        rebuildwikis.append([wiki])
                if len(rebuildwikis) > 1:
                    newwikis = str(rebuildwikis.join(","))
                    calldb("update users set wikis = '"+newwikis+"' where id="+str(id)+";","write")
                else:
                    newwikis = str(rebuildwikis)
                    calldb("update users set wikis = '"+newwikis+"' where id="+str(id)+";","write")
            else:
                calldb("update users set wikis = NULL where id="+str(id)+";","write")
checkAllPerms()
import mysql.connector
from mysql.connector import Error
import credentials
import re
import hashlib

import mwclient
import login

masterwiki =  mwclient.Site('en.wikipedia.org')
masterwiki.login(login.username,login.password)
metawiki =  mwclient.Site('meta.wikimedia.org')
metawiki.login(login.username,login.password)
ptwiki =  mwclient.Site('pt.wikipedia.org')
ptwiki.login(login.username,login.password)

def callAPI(params):
    return masterwiki.api(**params)

def callmetaAPI(params):
    return metawiki.api(**params)

def callptwikiAPI(params):
    return ptwikiwiki.api(**params)

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
def verifyusers():
    results = calldb("select * from wikitasks where task = 'verifyaccount';","read")
    for result in results:
        wtid=result[0]
        user = result[2]
        userresults = calldb("select * from users where id = '"+str(user)+"';","read")
        for userresult in userresults:
            username = userresult[2]
            params = {'action': 'query',
            'format': 'json',
            'meta': 'tokens'
            }
            raw = callAPI(params)
            try:code = raw["query"]["tokens"]["csrftoken"]
            except:
                print raw
                print "FAILURE: Param not accepted."
                quit()
            mash= username+credentials.secret
            confirmhash = hashlib.md5(mash.encode()) 
            params = {'action': 'emailuser',
            'format': 'json',
            'target': username,
            'subject': 'UTRS Wiki Account Verification',
            'token': code.encode(),
            'text': 
"""
Thank you for registering your account with UTRS. Please verify your account by going to the following link.

http://utrs-beta.wmflabs.org/verify/"""+str(confirmhash.hexdigest())+"""

Thanks,
UTRS Developers"""
            }
            raw = callAPI(params)
            calldb("update users set u_v_token = '"+confirmhash.hexdigest()+"' where id="+str(user)+";","write")
            calldb("delete from wikitasks where id="+str(wtid)+";","write")
            checkPerms(username,user)
def checkPerms(user, id):
    enperms = {"user":False,"sysop":False,"checkuser":False,"oversight":False}
    ptperms = {"user":False,"sysop":False,"checkuser":False,"oversight":False}
    metaperms = {"user":False,"steward":False,"staff":False}
    ##############################
    ###Enwiki checks##############
    params = {'action': 'query',
            'format': 'json',
            'list': 'users',
            'ususers': user,
            'usprop': 'groups|editcount'
            }
    raw = callAPI(params)
    results = raw["query"]["users"][0]["groups"]
    for result in results:
        if "sysop" in result:
            enperms["sysop"]=True
        if "checkuser" in result:
            enperms["checkuser"]=True
        if "oversight" in result:
            enperms["oversight"]=True
    params = {'action': 'query',
            'format': 'json',
            'meta': 'userinfo',
            'ususers': user,
            'usprop': 'groups'
            }
    raw = callAPI(params)
    editcount = raw["query"]["users"][0]["editcount"]
    if editcount >500:enperms["user"]=True
    ##############################
    ###Ptwiki checks##############
    raw = callptwikiAPI(params)
    results = raw["query"]["users"][0]["groups"]
    for result in results:
        if "sysop" in result:
            ptperms["sysop"]=True
        if "checkuser" in result:
            ptperms["checkuser"]=True
        if "oversight" in result:
            ptperms["oversight"]=True
    params = {'action': 'query',
            'format': 'json',
            'meta': 'userinfo',
            'ususers': user,
            'usprop': 'groups'
            }
    raw = callptwikiAPI(params)
    editcount = raw["query"]["users"][0]["editcount"]
    if editcount >500:enperms["user"]=True
    ##############################
    ###Meta checks##############
    params = {'action': 'query',
            'format': 'json',
            'list': 'globalallusers',
            'agufrom': user,
            'agulimit':1,
            'aguprop': 'groups'
            }
    raw = callmetaAPI(params)
    results = raw["query"]["globalallusers"][0]["groups"]
    for result in results:
        if "steward" in result:
            metaperms["steward"]=True
        if "staff" in result:
            metaperms["staff"]=True
    params = {'action': 'query',
            'format': 'json',
            'list': 'users',
            'ususers': user,
            'usprop': 'editcount'
            }
    raw = callptwikiAPI(params)
    editcount = raw["query"]["users"][0]["editcount"]
    if editcount >500:metaperms["user"]=True
    ###################################
    ###Set allowed Wikis###############
    string = ""
    if enperms['user']:
        string += "enwiki"
    if ptperms['user']:
        if string != "":string +=",ptwiki"
        else:string +="ptwiki"
    if metaperms['user']:
        if string != "":string +=",global"
        else:string +="global"
    calldb("update users set wikis = '"+string+"' where id="+str(id)+";","write")
    ###################################
    ###Set permissions#################
    if enperms['user']:
        calldb("insert into permissions (userid,wiki,oversight,checkuser,admin,user) values ("+id+",enwiki,"+int(enperms["oversight"])+","+int(enperms["checkuser"])+","+int(enperms["sysop"])+",1);","write")
    if ptperms['user']:
        calldb("insert into permissions (userid,wiki,oversight,checkuser,admin,user) values ("+id+",ptwiki,"+int(ptperms["oversight"])+","+int(ptperms["checkuser"])+","+int(ptperms["sysop"])+",1);","write")
    if metaperms['user']:
        calldb("insert into permissions (userid,wiki,steward,staff,user) values ("+id+",*,"+int(metaperms["steward"])+","+int(metaperms["staff"])+",1);","write")
verifyusers()

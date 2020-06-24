#!/usr/bin/env python
# -*- coding: utf-8 -*-
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
metawiki =  mwclient.Site('meta.wikimedia.org')
metawiki.login(login.username,login.password)
ptwiki =  mwclient.Site('pt.wikipedia.org')
ptwiki.login(login.username,login.password)

regex = "((^\s*((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))\s*$)|(^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$))"

def callAPI(params):
    return masterwiki.api(**params)

def callmetaAPI(params):
    return metawiki.api(**params)

def callptwikiAPI(params):
    return ptwiki.api(**params)

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
        userresults = calldb("select id,username from users where id = '"+str(user)+"';","read")[0]
        
        try:username = str(userresults[1])
        except:username = userresults[1]
        userpage = "User talk:"+username
        userresult = calldb("select * from users where id = '"+str(user)+"';","read")[0]
        if userresult[6] == None:
            params = {'action': 'query',
            'format': 'json',
            'list': 'users',
            'ususers': username
            }
            raw = callAPI(params)
            try:userexist = raw["query"]["users"][0]["userid"]
            except:
                calldb("delete from wikitasks where id="+str(wtid)+";","write")
                calldb("delete from users where id="+str(user)+";","write")
                print "ACCOUNT DELETION: " + username
                continue
            page = masterwiki.pages[userpage]
            page.save(page.text() + """
== Your UTRS Account ==
You have no wikis in which you meet the requirements for UTRS. Your account has been removed and you will be required to reregister once you meet the requirements. If you are blocked on any wiki that UTRS uses, please resolve that before registering agian also. ~~~~
                        """, "UTRS Account - Does not meet requirements")
            calldb("delete from wikitasks where id="+str(wtid)+";","write")
            calldb("delete from users where id="+str(user)+";","write")
            print "ACCOUNT DELETION: " + username
            continue
        if "," in userresult[6]:
            for wiki in userresult[6].split(","):
                if checkBlock(username,wiki):
                    try:userpage = "User talk:"+username
                    except:
                        userpage = "User talk:"+str(username)
                    page = masterwiki.pages[userpage]
                    page.save(page.text() + """
== Your UTRS Account ==
You are currently blocked on one of the sites UTRS does appeals for and therefore you can't access appeals. Your account has been removed. ~~~~
                        """, "UTRS Account for blocked users")
                    calldb("delete from wikitasks where id="+str(wtid)+";","write")
                    calldb("delete from users where id="+str(user)+";","write")
                    print "ACCOUNT DELETION: " + username
                    continue

        else:
            if checkBlock(username,userresult[6]):
                try:userpage = "User talk:"+username
                except:
                    userpage = "User talk:"+str(username)
                page = masterwiki.pages[userpage]
                page.save(page.text() + """
== Your UTRS Account ==
You are currently blocked on one of the sites UTRS does appeals for and therefore you can't access appeals. Your account has been removed. ~~~~
                    """, "UTRS Account for blocked users")
                calldb("delete from wikitasks where id="+str(wtid)+";","write")
                calldb("delete from users where id="+str(user)+";","write")
                print "ACCOUNT DELETION: " + username
                continue
        calldb("delete from wikitasks where id="+str(wtid)+";","write")
def checkBlock(target,wiki):
    if wiki == "enwiki" or wiki == "ptwiki":
        params = {'action': 'query',
        'format': 'json',
        'list': 'blocks',
        'bkusers': target
        }
        raw = runAPI(wiki, params)
        if len(raw["query"]["blocks"])>0:
            return True
        else:
            return False
    if wiki == "global":
        params = {'action': 'query',
        'format': 'json',
        'list': 'globalallusers',
        'agufrom': target,
        'agulimit':1,
        'aguprop':'lockinfo'
        }
        raw = runAPI(wiki, params)
        try:
            if raw["query"]["globalallusers"][0]["locked"]=="":return True
        except:
            return False
def blockNotFound(username,wiki,id):
    print "Block not found email: " + username
    mash= username+credentials.secret
    confirmhash = hashlib.md5(mash.encode()).hexdigest()
    subject="UTRS Appeal #"+str(id)+" - Block not found"
    text="""
Your block that you filed an appeal for on the UTRS Platform has not been found. Please verify the name or IP address being blocked.

http://"""+credentials.utrshost+""".wmflabs.org/fixblock/"""+str(confirmhash)+"""

Thanks,
UTRS Developers"""
    sendemail(username,subject,text,wiki)

def runAPI(wiki, params):
    if wiki == "enwiki":raw = callAPI(params)
    if wiki == "ptwiki":raw = callptwikiAPI(params)
    if wiki == "global":raw = callmetaAPI(params)
    return raw
def updateBlockinfoDB(raw,appeal,wiki):
    if wiki != "global":
        blockingadmin = raw["query"]["blocks"][0]["by"]
        reason = raw["query"]["blocks"][0]["reason"]
        reason = reason.replace("'","\'")
    else:
        blockingadmin = raw["query"]["logevents"][0]["user"]
        reason = raw["query"]["logevents"][0]["comment"]
        reason = reason.replace("'","\'")
    calldb("update appeals set blockfound = 1 where id="+str(appeal[0])+";","write")
    calldb("update appeals set blockingadmin = '"+blockingadmin+"' where id="+str(appeal[0])+";","write")
    calldb("update appeals set blockreason = '"+reason+"' where id="+str(appeal[0])+";","write")
    results = calldb("select * from appeals where status = 'VERIFY';","read")
    if results[0][2] != results[0][3]:calldb("update appeals set status = \"PRIVACY\" where id="+str(appeal[0])+";","write")
    else:calldb("update appeals set status = \"OPEN\" where id="+str(appeal[0])+";","write")
def sendemail(target,subject,text,wiki):
    params = {'action': 'query',
            'format': 'json',
            'meta': 'tokens'
            }
    raw = runAPI(wiki, params)
    try:code = raw["query"]["tokens"]["csrftoken"]
    except:
        print raw
        print "FAILURE: Param not accepted."
        quit()
    params = {'action': 'emailuser',
    'format': 'json',
    'target': target,
    'subject': subject,
    'token': code.encode(),
    'text': text
            }
    try:raw = callAPI(params)
    except:
        print "Couldn't send email"
        print [target,subject,text,wiki]
def clearPrivateData():
    results = calldb("select * from privatedatas;","read")
    for result in results:
        id = result[1]
        appeal = calldb("select id,status from appeals where id = "+str(id)+";","read")
        if appeal[0][1] not in ["DECLINE","EXPIRE","ACCEPT","INVALID"]:continue
        logs = calldb("select timestamp from logs where referenceobject = "+str(id)+" and (action RLIKE 'closed' or action LIKE '%decline' or action LIKE '%accept' or action LIKE '%expired' or action LIKE '%invalid') and objecttype = 'appeal';","read")
        if datesince(logs[0], 7):
            calldb("delete from privatedatas where appealID = "+str(id)+";","write")
def appeallist():
    fulltext=""
    top = """
    {| align="center" class="wikitable sortable" style="align: center; float:center; font-size: 90%; text-align:center" cellspacing="0" cellpadding="1" valign="middle" |-
    !Appeal Number
    !Appellant
    !Appeal Filed
    !Status
    """
    fulltext+=top
    results = calldb("select id,appealfor,submitted,status from appeals where status != 'CLOSED' AND status !='VERIFY' AND status != 'NOTFOUND' AND status != 'EXPIRE' AND status != 'DECLINE' AND status != 'ACCEPT' AND status != 'INVALID' AND wiki = 'enwiki';","read")
    for result in results:
        username = result[1].encode('utf-8').strip()
        if username.startswith('#'):
            fulltext += "\n|-\n|[https://"+credentials.utrshost+".wmflabs.org/appeal/"+str(result[0])+" "+str(result[0])+"]\n|"+"[https://en.wikipedia.org/wiki/Special:BlockList?wpTarget="+username.replace('#','%23')+" Block ID "+username+"]\n|"+str(result[3])+"\n|"+str(result[4])
        else:
            fulltext += "\n|-\n|[https://"+credentials.utrshost+".wmflabs.org/appeal/"+str(result[0])+" "+str(result[0])+"]\n|"+"[[User talk:"+username+"|"+username+"]]\n|"+str(result[3])+"\n|"+str(result[4])
    fulltext +="\n|}"
    page = masterwiki.pages["User:DeltaQuad/UTRS Appeals"]
    page.save(fulltext, "Updating UTRS caselist")
def datesince(orig,length):
    today = datetime.now()
    diff = today - timedelta(days=length)
    return diff > orig[0]
def closeNotFound():
    results = calldb("select id from appeals where status = 'NOTFOUND';","read")
    for result in results:
        id = result[0]
        logs = calldb("select timestamp from logs where referenceobject = "+str(id)+" and action = 'create' and objecttype = 'appeal';","read")
        if datesince(logs[0], 2):
            calldb("update appeals set status = 'EXPIRE' where id = "+str(id)+";","write")
            calldb("insert into logs (user, referenceobject,objecttype, action, ip, ua, protected) VALUES ('"+str(0)+"','"+str(id)+"','appeal','closed - expired','DB entry','DB/Python',0);","write")
verifyusers()
clearPrivateData()
appeallist()
closeNotFound()

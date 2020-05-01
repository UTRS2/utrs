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
        userresults = calldb("select * from users where id = '"+str(user)+"';","read")
        for userresult in userresults:
            username = userresult[1]
            if username == None:continue
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
            try:raw = callAPI(params)
            except:
                page = masterwiki.pages["User talk:"+str(username)]
                page.save(page.text() + """
== Your UTRS Account ==
Right now you do not have wiki email enabled on your onwiki account, and therefore we are unable to verify you are who you say you are. To prevent duplicate notices to your talkpage about this, the account has been deleted and you will need to reregister. ~~~~
                    """, "UTRS Account notice")
                calldb("delete from wikitasks where id="+str(wtid)+";","write")
                calldb("delete from users where id="+str(user)+";","write")    
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
            'usprop': 'groups|editcount|emailable'
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
    editcount = raw["query"]["users"][0]["editcount"]
    if editcount >500:ptperms["user"]=True
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
        calldb("insert into permissions (userid,wiki,oversight,checkuser,admin,user) values ("+str(id)+",'enwiki',"+str(int(enperms["oversight"]))+","+str(int(enperms["checkuser"]))+","+str(int(enperms["sysop"]))+",1);","write")
    if ptperms['user']:
        calldb("insert into permissions (userid,wiki,oversight,checkuser,admin,user) values ("+str(id)+",'ptwiki',"+str(int(ptperms["oversight"]))+","+str(int(ptperms["checkuser"]))+","+str(int(ptperms["sysop"]))+",1);","write")
    if metaperms['user']:
        calldb("insert into permissions (userid,wiki,steward,staff,user) values ("+str(id)+",'*',"+str(int(metaperms["steward"]))+","+str(int(metaperms["staff"]))+",1);","write")
def verifyblock():
    results = calldb("select * from appeals where status = 'VERIFY';","read")
    for appeal in results:
        ip = calldb("select * from privatedatas where appealID = "+str(appeal[0])+";","read")[0][2]
        target = appeal[1]
        wiki=appeal[13]
        blocktype = appeal[4]
        if wiki == "enwiki" or wiki == "ptwiki":
            if blocktype == 2:target = ip
            if blocktype == 1:
                params = {'action': 'query',
                'format': 'json',
                'list': 'blocks',
                'bkusers': target
                }
                raw = runAPI(wiki, params)
                if len(raw["query"]["blocks"])>0:
                    updateBlockinfoDB(raw,appeal)
                    continue
                else:
                    calldb("update appeals set status = 'NOTFOUND' where id="+str(appeal[0])+";","write")
                    try:blockNotFound(target,wiki,appeal[0])
                    except:calldb("update appeals set status = 'INVALID' where id="+str(appeal[0])+";","write")
            else:
                params = {'action': 'query',
                'format': 'json',
                'list': 'blocks',
                'bkip': target
                }
                raw = runAPI(wiki, params)
                if len(raw["query"]["blocks"])>0:
                    updateBlockinfoDB(raw,appeal)
                    continue
                else:
                    params = {'action': 'query',
                    'format': 'json',
                    'list': 'blocks',
                    'bkids': target
                    }
                    try:raw = runAPI(wiki, params)
                    except:
                        calldb("update appeals set status = 'NOTFOUND' where id="+str(appeal[0])+";","write")
                        if re.match(regex,target) == None:blockNotFound(target,wiki,appeal[0])
                        continue
                    if len(raw["query"]["blocks"])>0:
                        updateBlockinfoDB(raw,appeal)
                        continue
                    else:
                        calldb("update appeals set status = 'NOTFOUND' where id="+str(appeal[0])+";","write")
                        if re.match(regex,target) == None:blockNotFound(target,wiki,appeal[0])
                        continue
        if wiki == "global":
            params = {'action': 'query',
            'format': 'json',
            'list': 'globalallusers ',
            'agufrom': target,
            'agulimit':1,
            'aguprop':'lockinfo'
            }
            raw = runAPI(wiki, params)
            try:
                if raw["query"]["globalallusers"][0]["locked"]=="":locked=True
                params = {'action': 'query',
                'format': 'json',
                'list': 'logevents',
                'lefrom': "User:"+target+"@global",
                'letype':'globalauth',
                'lelimit':1,
                'leprop':'user|comment'
                }
                raw = runAPI(wiki, params)
                updateBlockinfoDB(raw,appeal)
                continue
            except:
                params = {'action': 'query',
                'format': 'json',
                'list': 'globallocks ',
                'bgip': target,
                'bglimit':1,
                'bgprop':'lockinfo'
                }
                raw = runAPI(wiki, params)
                if len(raw["query"]["globalblocks"])>0:
                    updateBlockinfoDB(raw,appeal)
                    continue
                else:
                    calldb("update appeals set status = 'NOTFOUND' where id="+str(appeal[0])+";","write")
                    if re.match(regex,appeal[0]) == None:blockNotFound(target,wiki,appeal[0])
                    continue
def blockNotFound(username,wiki,id):
    print "Block not found email: " + username
    mash= username+credentials.secret
    confirmhash = hashlib.md5(mash.encode()) 
    subject="UTRS Appeal #"+str(id)+" - Block not found"
    text="""
Your block that you filed an appeal for on the UTRS Platform has not been found. Please verify the name or IP address being blocked.

http://utrs-beta.wmflabs.org/fixblock/"""+str(confirmhash)+"""

Thanks,
UTRS Developers"""
    sendemail(username,subject,text,wiki)

def runAPI(wiki, params):
    if wiki == "enwiki":raw = callAPI(params)
    if wiki == "ptwiki":raw = callptwikiAPI(params)
    if wiki == "global":raw = callmetaAPI(params)
    return raw
def updateBlockinfoDB(raw,appeal):
    calldb("update appeals set blockfound = 1 where id="+str(appeal[0])+";","write")
    calldb("update appeals set blockingadmin = '"+raw["query"]["blocks"][0]["by"]+"' where id="+str(appeal[0])+";","write")
    calldb("update appeals set blockreason = '"+raw["query"]["blocks"][0]["reason"]+"' where id="+str(appeal[0])+";","write")
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
    raw = callAPI(params)
def clearPrivateData():
    results = calldb("select * from privatedatas;","read")
    for result in results:
        id = result[1]
        appeal = calldb("select * from appeals where id = "+str(id)+";","read")
        if appeal[0][5] != "CLOSED":continue
        logs = calldb("select timestamp from logs where referenceobject = "+str(id)+" and action = 'closed' and objecttype = 'appeal';","read")
        if datesince(logs[0]["timestamp"], 7):
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
    results = calldb("select * from appeals where status != 'CLOSED' AND status !='VERIFY' AND status != 'NOTFOUND' AND status != 'EXPIRED' AND status != 'DECLINE' AND status != 'ACCEPT';","read")
    for result in results:
        fulltext += "\n|-\n|[https://utrs-beta.wmflabs.org/appeal/"+str(result[0])+" "+str(result[0])+"]\n|"+str(result[1])+"\n|"+str(result[9])+"\n|"+str(result[5])
    fulltext +="\n|}"
    page = masterwiki.pages["User:DeltaQuad/UTRS Appeals"]
    page.save(fulltext, "Updating UTRS caselist")
def datesince(orig,length):
    today = date.today()
    diff = today - timedelta(days=length)
    orig = datetime.strptime(orig,'%Y-%m-%d %H:%M:%S')
    return diff > today
def closeNotFound():
    results = calldb("select * from appeals where status = NOTFOUND;","read")
    for result in results:
        id = result[0]
        logs = calldb("select timestamp from logs where referenceobject = "+str(id)+" and action = 'create' and objecttype = 'appeal';","read")
        if datesince(logs[0]["timestamp"], 5):
            calldb("update appeals set status = 'EXPIRED' where appealID = "+str(id)+";","write")
verifyusers()
verifyblock()
clearPrivateData()
appeallist()

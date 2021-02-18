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
ptwiki =  mwclient.Site('pt.wikipedia.org')

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
def runAPI(wiki, params):
    if wiki == "enwiki":raw = callAPI(params)
    if wiki == "ptwiki":raw = callptwikiAPI(params)
    if wiki == "global":raw = callmetaAPI(params)
    return raw
def clearPrivateData():
    results = calldb("select * from privatedatas;","read")
    for result in results:
        id = result[1]
        appeal = calldb("select id,status from appeals where id = "+str(id)+";","read")
        if appeal[0][1] not in ["DECLINE","EXPIRE","ACCEPT","INVALID"]:continue
        logs = calldb("select timestamp from log_entries where model_id = "+str(id)+" and (action RLIKE 'closed' or action LIKE '%decline' or action LIKE '%accept' or action LIKE '%expire%' or action LIKE '%invalid') and model_type = 'App\\\\Models\\\\Appeal';","read")
        if datesince(logs[0], 7):
            calldb("delete from privatedatas where appealID = "+str(id)+";","write")
def datesince(orig,length):
    today = datetime.now()
    diff = today - timedelta(days=length)
    return diff > orig[0]
clearPrivateData()

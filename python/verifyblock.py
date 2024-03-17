import mwclient
import mysql.connector
import config
import ipaddress

# get any appeals that need to be verified from the database
mysql = mysql.connector.connect(
    host=config.mysql_host,
    user=config.mysql_user,
    password=config.mysql_password,
    database=config.mysql_database
)

cursor = mysql.cursor()
cursor.execute("SELECT * FROM appeals WHERE status = 'VERIFY';")
appeals = cursor.fetchall()

#connect to each wiki and throw the connection into a dictionary
sites = {}
sites["enwiki"] = mwclient.Site('en.wikipedia.org')
sites["metawiki"] = mwclient.Site('meta.wikimedia.org')
sites["ptwiki"] = mwclient.Site('pt.wikipedia.org')

#run through appeals from the database
for appeal in appeals:
    print(appeal)
    if appeal[16] == 1:
        wiki = "enwiki"
    elif appeal[16] == 2:
        wiki = "metawiki"
    elif appeal[16] == 3:
        wiki = "ptwiki"
    else:
        raise ValueError("Unknown wiki ID: " + str(appeal[16]))
    
    #check the API for a block against appeal[1]
    if appeal[3] == 0:
        #if the appeal is an IP, use bkip
        try:
            ipaddress.ip_address(appeal[1])
            block = sites[wiki].api('query', list='blocks', bkip=appeal[1])["query"]
        except ValueError:
            break
    elif appeal[3] == 1:
        #if the appeal is a username, use bkusers
        block = sites[wiki].api('query', list='blocks', bkusers=appeal[1])["query"]
    elif appeal[3] == 2:
        #if the appeal is an ip underneath a range block, use bkip
        try:
            ipaddress.ip_address(appeal[1])
            block = sites[wiki].api('query', list='blocks', bkip=appeal[13])["query"]
        except ValueError:
            break
    else:
        raise ValueError("Unknown appeal type: " + str(appeal[3]))
    print(block)
    if block["blocks"]:
        #if there is a block, update the database
        cursor.execute("UPDATE appeals SET status = 'OPEN' WHERE id = " + str(appeal[0]) + ";")
        cursor.execute("UPDATE appeals SET blockfound = 1 WHERE id = " + str(appeal[0]) + ";")
        print('update - blockfound')
        mysql.commit()
    else:
        #if there is no block, update the database
        cursor.execute("UPDATE appeals SET status = 'NOTFOUND' WHERE id = " + str(appeal[0]) + ";")
        cursor.execute("UPDATE appeals SET blockfound = -1 WHERE id = " + str(appeal[0]) + ";")
        print('update - no block')
        mysql.commit()


    

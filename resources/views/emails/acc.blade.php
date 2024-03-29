@extends('layouts.email')
@section('content')
<table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-2" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
    <tr>
    <td class="pad" style="padding-left:30px;padding-right:10px;padding-top:10px;">
    <div style="color:#555555;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;font-size:24px;line-height:120%;text-align:left;mso-line-height-alt:28.799999999999997px;">
    <p style="margin: 0; word-break: break-word;"><strong><span>{{__('emails.introduction')}}</span></strong></p>
    </div>
    </td>
    </tr>
    </table>
    <table border="0" cellpadding="0" cellspacing="0" class="paragraph_block block-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
    <tr>
    <td class="pad" style="padding-bottom:5px;padding-left:30px;padding-right:10px;">
    <div style="color:#555555;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;font-size:14px;line-height:120%;text-align:left;mso-line-height-alt:16.8px;">
    <p style="margin: 0; word-break: break-word;"><span><span>{{__('emails.acc.text')}}</span></span></p>
    <br />
    <p style="margin: 0; word-break: break-word;"><a href="{{$url}}" style="text-decoration:none;display:inline-block;color:#000000;background-color:#F8D995;border-radius:3px;width:auto;border-top:0px solid transparent;font-weight:undefined;border-right:0px solid transparent;border-bottom:0px solid transparent;border-left:0px solid transparent;padding-top:5px;padding-bottom:5px;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;font-size:12px;text-align:center;mso-border-alt:none;word-break:keep-all;"><span style="padding-left:20px;padding-right:20px;font-size:12px;display:inline-block;letter-spacing:normal;"><span style="word-break: break-word; line-height: 24px;">{{__('emails.acc-continue-button')}}<br/></span></span></div></a><!--[if mso]></center></v:textbox></v:roundrect><![endif]--></p>
    <br /><br /><br /><p style="margin: 0; word-break: break-word;"><span>{{__('emails.footer.not-you-attempt')}}</span></p>
    <a href="{{$stopUrl}}" style="text-decoration:none;display:inline-block;color:#000000;background-color:#F8D995;border-radius:3px;width:auto;border-top:0px solid transparent;font-weight:undefined;border-right:0px solid transparent;border-bottom:0px solid transparent;border-left:0px solid transparent;padding-top:5px;padding-bottom:5px;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;font-size:12px;text-align:center;mso-border-alt:none;word-break:keep-all;"><span style="padding-left:20px;padding-right:20px;font-size:12px;display:inline-block;letter-spacing:normal;"><span style="word-break: break-word; line-height: 24px;">{{__('emails.footer.not-you-button')}}<br/></span></span></div></a><!--[if mso]></center></v:textbox></v:roundrect><![endif]-->
    </div>
    </td>
    </tr>
    </table>
    <table border="0" cellpadding="0" cellspacing="0" class="button_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
    <tr>
    <td class="pad" style="padding-bottom:10px;padding-left:30px;padding-right:10px;padding-top:10px;text-align:left;">
    <div align="left" class="alignment"><!--[if mso]>
    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" style="height:34px;width:220px;v-text-anchor:middle;" arcsize="9%" stroke="false" fillcolor="#F8D995">
    <w:anchorlock/>
    <v:textbox inset="0px,0px,0px,0px">
    <center style="color:#ffffff; font-family:Arial, sans-serif; font-size:12px">
    <![endif]-->
    </div>
    </td>
    </tr>
    </table>
    <div class="spacer_block block-5" style="height:30px;line-height:30px;font-size:1px;"> </div>
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody>
    </table>
    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row row-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
    <tbody>
    <tr>
    <td>
    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; color: #000000; background-color: #EDEDED; width: 615.00px; margin: 0 auto;" width="615.00">
    <tbody>
    <tr>
    <td class="column column-1" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;" width="100%">
    <table border="0" cellpadding="30" cellspacing="0" class="paragraph_block block-1" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
    <tr>
    <td class="pad">
    <div style="color:#555555;font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;font-size:14px;line-height:180%;text-align:left;mso-line-height-alt:25.2px;">
    <p style="margin: 0; word-break: break-word;"><span>{{__('emails.footer.salutation')}}</span></p>
    <p style="margin: 0; word-break: break-word;"><strong>{{__('emails.footer.name-developers')}}</strong></p>
    </div>
    </td>
    </tr>
    </table>
    @endsection
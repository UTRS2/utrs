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
    <p style="margin: 0; word-break: break-word;"><span><span>{{__('emails.appealkey.text')}}</span></span></p>
    <br />
    <p style="margin: 0; word-break: break-word;"><span><span>{{$appealkey}}</span></span></p>
    @component('components.stop-spam', ['stopUrl' => $stopUrl])
    @endcomponent
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
    @endsection
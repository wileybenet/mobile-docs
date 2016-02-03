/* ***** BEGIN LICENSE BLOCK *****
 * Distributed under the BSD license:
 *
 * Copyright (c) 2012, Ajax.org B.V.
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of Ajax.org B.V. nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL AJAX.ORG B.V. BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * Contributor(s):
 * 
 *
 *
 * ***** END LICENSE BLOCK ***** */

ace.define('ace/mode/wwbnet', ['require', 'exports', 'module', 'ace/lib/oop', 'ace/mode/text', 'ace/tokenizer', 'ace/mode/wwbnet_highlight_rules'], function (require, exports, module) {


var oop = require("../lib/oop");
var TextMode = require("./text").Mode;
var Tokenizer = require("../tokenizer").Tokenizer;
var WwbnetHighlightRules = require("./wwbnet_highlight_rules").WwbnetHighlightRules;

var Mode = function() {
    this.HighlightRules = WwbnetHighlightRules;
};
oop.inherits(Mode, TextMode);

(function() {
       
    this.lineCommentStart = ["'", "REM"];
    
    this.$id = "ace/mode/wwbnet";
}).call(Mode.prototype);

exports.Mode = Mode;
});


ace.define('ace/mode/wwbnet_highlight_rules', ['require', 'exports', 'module', 'ace/lib/oop', 'ace/mode/text_highlight_rules'], function (require, exports, module) {


var oop = require("../lib/oop");
var TextHighlightRules = require("./text_highlight_rules").TextHighlightRules;

var WwbnetHighlightRules = function() {

    this.$rules = {
    "start": [
        {
            token: [
                "meta.ending-space"
            ],
            regex: "$"
        },
        {
            token: [
                null
            ],
            regex: "^(?=\\t)",
            next: "state_3"
        },
        {
            token: [null],
            regex: "^(?= )",
            next: "state_4"
        },
        {
            token: [
                "text",
                "storage.type.function.wwbnet",
                "text",
                "entity.name.function.wwbnet",
                "text",
                "punctuation.definition.parameters.wwbnet",
                "variable.parameter.function.wwbnet",
                "punctuation.definition.parameters.wwbnet"
            ],
            regex: "^(\\s*)(Function|Sub|Property)(\\s*)([a-zA-Z_]\\w*)(\\s*)(\\()([^)]*)(\\))"
        },
        {
            token: "punctuation.definition.comment.wwbnet",
            regex: "'|\\bRem\\b",
            caseInsensitive: true,
            next: "comment"
        },
        {
            token: "storage.declaration.wwbnet",
            regex: "\\b(?:Attribute|Class|Const|Declare|Delegate|Dim|EntryPoint|Enum|End\\sEnum|EntryPoint|Event|Function|End\\sFunction|Implements|Imports|Module|LoadModule|Optimizer|Option|Private|Project|End\\sProject|Property|End\\sProperty|Public|ReDim|ReferenceProject|Static|Structure|End\\sStructure|Sub|End\\sSub|WithEvents)\\b",
            caseInsensitive: true
        },
        {
            token: "storage.modifier.wwbnet",
            regex: "\\b(?:Default|Friend|Private|Public|ReadOnly|WriteOnly)\\b",
            caseInsensitive: true
        },
        {
            token: "storage.type.wwbnet",
            regex: "\\b(?:Any|Boolean|Byte|Char|Date|Decimal|Double|Integer|Long|Object|SByte|Short|Single|String|UInteger|ULong|UShort)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.control.wwbnet",
            regex: "\\b(?:AddHandler|Call|CallByName|Continue\\s(?:Do|For|While)|Do|Until|Loop|End|Exit\\s(?:All|Do|For|Function|Property|Sub|Try|While)|For(?:\\sEach)?|Next|To|GoTo|If|Then|Else|ElseIf|End\\sIf|Lock|End\\sLock|MacroCheck|MacroCheckThis|MacroRun|MacroRunThis|ModuleLoad|ModuleLoadThis|RaiseEvent|RemoveHandler|Return|Select\\sCase|Case|End\\sSelect|Stop|Using|End\\sUsing|While|End\\sWhile|With|End\\sWith)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.error.wwbnet",
            regex: "\\b(?:Err|Error|ErrorToString|On\\sError\\s(?:GoTo|Resume)|Resume|Throw|Try|Catch|Finally|End\\sTry)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.conversion.wwbnet",
            regex: "\\b(?:CBool|CByte|CChar|CDate|CDec|CDbl|CInt|CLng|CObj|CSByte|CShort|CSng|CStr|CType|CUInt|CULng|CUShort|CVErr|DirectCast|TryCast|Val)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.varinfo.wwbnet",
            regex: "\\b(?:IsArray|IsDate|IsDBNull|IsError|IsNothing|IsNumeric|IsReference|LBound|SystemTypeName|TypeName|UBound|VarType|VbTypeName)\\b",
            caseInsensitive: true
        },
        {
            token: "constant.language.wwbnet",
            regex: "\\b(?:False|Nothing|True|Win16|Win32|Win64)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.math.wwbnet",
            regex: "\\b(?:Abs|Atn|Cos|Exp|Fix|Int|Log|Randomize|Rnd|Round|Sgn|Sin|Sqr|Tan)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.string.wwbnet",
            regex: "\\b(?:Asc|AscW|Chr|ChrW|Format|GetChar|Hex|InStr|InStrRev|Join|LCase|Left|Len|LSet|LTrim|Mid|Oct|Replace|Right|RSet|RTrim|Space|Split|Str|StrComp|StrConv|StrDup|StrReverse|Trim|UCase)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.object.wwbnet",
            regex: "\\b(?:CreateObject|GetModuleInstance|GetObject|Me)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.timedate.wwbnet",
            regex: "\\b(?:DateAdd|DateDiff|DatePart|DateSerial|DateValue|Day|Hour|Minute|Month|MonthName|Now|Second|Timer|TimeSerial|TimeValue|Weekday|WeekdayName|Year)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.file.wwbnet",
            regex: "\\b(?:ChDir|ChDrive|CurDir|Dir|EOF|FileAttr|FileClose|FileCopy|FileDateTime|FileLen|FileOpen|FreeFile|Get|GetAttr|Input|InputString|Kill|Line\\sInput|LineInput|Loc|LOF|MkDir|Print|PrintLine|Put|Reset|Rename|RmDir|Seek|Seek|SetAttr|Write|WriteLine)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.userinput.wwbnet",
            regex: "\\b(?:Dialog|GetFilePath|InputBox|MsgBox|ShowPopupMenu)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.userdialog.wwbnet",
            regex: "\\b(?:Begin\\sDialog|End\\sDialog|CancelButton|CheckBox|ComboBox|DropListBox|GroupBox|ListBox|MultiListBox|OKButton|OptionButton|OptionGroup|Picture|PushButton|Text|TextBox)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.dialogfunc.wwbnet",
            regex: "\\b(?:DlgControlId|DlgCount|DlgEnable|DlgEnd|DlgFocus|DlgListBoxArray|DlgName|DlgNumber|DlgSetPicture|DlgText|DlgType|DlgValue|DlgVisible)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.dde.wwbnet",
            regex: "\\b(?:DDEExecute|DDEInitiate|DDEPoke|DDERequest|DDETerminate|DDETerminateAll)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.settings.wwbnet",
            regex: "\\b(?:DeleteSetting|GetAllSettings|GetSetting|SaveSetting)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.miscellaneous.wwbnet",
            regex: "\\b(?:AboutWinWrapBasic|AppActivate|Assign|Attribute|Beep|CallersLine|CallersSymbol|Choose|Clipboard|Command|Decode64|Decode64B|Decrypt64|Decrypt64B|Debug|DoEvents|Encode64|Encode64B|Encrypt64|Encrypt64B|Environ|Eval|If|IIf|GetLocale|KeyName|MacroDir|QBColor|Rem|RGB|SendKeys|SetLocale|Shell|Wait)\\b",
            caseInsensitive: true
        },
        {
            token: "keyword.operator.wwbnet",
            regex: "\\b(?:Mod|Like|New|TypeOf|Not|And|AndAlso|Or|OrElse|Xor|Is|IsNot|GetType|AddressOf)\\b",
            caseInsensitive: true
        },
        {
            token: "punctuation.definition.string.begin.wwbnet",
            regex: '"',
            next: "string"
        },
        {
            token: "punctuation.definition.variable.wwbnet",
            regex: "(\\$)[a-zA-Z_x7f-xff][a-zA-Z0-9_x7f-xff]*?\\b\\s*"
        },
        {
            token: [
                "constant.numeric.wwbnet"
            ],
            regex: "-?\\b(?:(?:0(?:x|X)[0-9a-fA-F]*)|(?:(?:[0-9]+\\.?[0-9]*)|(?:\\.[0-9]+))(?:(?:e|E)(?:\\+|-)?[0-9]+)?)(?:L|l|UL|ul|u|U|F|f)?\\b"
        },
        {
            token: "entity.name.function.wwbnet",
            regex: "(?:(\\b[a-zA-Z_x7f-xff][a-zA-Z0-9_x7f-xff]*?\\b)(?=\\(\\)?))"
        },
        {
            token: "keyword.operator.wwbnet",
            regex: "\\+|\\-|\\^|\\*|\\/|\\\|\\<\\<|\\>\\>|\\&|\\=|\\<\\>|\\<|\\>|\\<\\=|\\>\\="
        },
        {
            token: "keyword.assignment.wwbnet",
            regex: "\\+\\=|\\-\\=|\\^\\=|\\*\\=|\\/\\=|\\\\\=|\\<\\<\\=|\\>\\>\\=|\\&\\=",
            caseInsensitive: true
        }
    ],
    "state_3": [
        {
            token: [
                "meta.odd-tab.tabs",
                "meta.even-tab.tabs"
            ],
            regex: "(\\t)(\\t)?"
        },
        {
            token: "meta.leading-space",
            regex: "(?=[^\\t])",
            next: "start"
        },
        {
            token: "meta.leading-space",
            regex: ".",
            next: "state_3"
        }
    ],
    "state_4": [
        {
            token: ["meta.odd-tab.spaces", "meta.even-tab.spaces"],
            regex: "(  )(  )?"
        },
        {
            token: "meta.leading-space",
            regex: "(?=[^ ])",
            next: "start"
        },
        {
            defaultToken: "meta.leading-space"
        }
    ],
    "comment": [
        {
            token: "comment.line.apostrophe.wwbnet",
            regex: "$|(?=(?:%>))",
            next: "start"
        },
        {
            defaultToken: "comment.line.apostrophe.wwbnet"
        }
    ],
    "string": [
        {
            token: "constant.character.escape.apostrophe.wwbnet",
            regex: '""'
        },
        {
            token: "string.quoted.double.wwbnet",
            regex: '"',
            next: "start"
        },
        {
            defaultToken: "string.quoted.double.wwbnet"
        }
    ]
}

};

oop.inherits(WwbnetHighlightRules, TextHighlightRules);

exports.WwbnetHighlightRules = WwbnetHighlightRules;
});
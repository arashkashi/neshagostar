<?php
/**
* xinha wrapper
* @version $Id: wysiwygXinhaV1.class.inc.php 465 2009-11-23 07:16:52Z salek $
* @changes
*   - xinha ExtendedFileManager plugin now supports php custom session hanlder, patch is inside patches folder
* 	- isEmpty function added to check xinha result and tell if it's really empty or not and
* 	  empty means nothing or just a <p> or <br/> tag
* 	- support default paramters for editors, it's possible to set for example
* 		imagesUrl or templateName as default for all of the editors,
* 		if they leave it alone default value will be used automatically
* 	- making full template of all of the xinha paramters
* @todo checking if xinhaV0 in packages included or not, if not throw an error an ask user to include it.
* @author sina salek
*/

define ('CMF_WysiwygV1_Ok',true);
define ('CMF_WysiwygV1_Error',2);

class cmfcWysiwygXinhaV1 extends cmfcClassesCore {
	var $_prefix='cmfXinha';
	
	/**
	* for multilingual website you can change this messages easily
	* @notice do not include following code into class initializing array, because messages name (definitions)
	* 			define after object initializing
	*/
	var $_messagesValue=array(
		CMF_WysiwygV1_Error=>'Unknown error',
		CMF_WysiwygV1_Is_Not_Valid_Email=>'"__value__" in __title__ is not valid email'
	);
	
	/**
	* You must set _editor_url to the URL (including trailing slash) where
	* where xinha is installed, it's highly recommended to use an absolute URL
	* eg: _editor_url = "/path/to/xinha/";
	* You may try a relative URL if you wish]
	* eg: _editor_url = "../";
	* in this example we do a little regular expression to find the absolute path.
	* _editor_url  = document.location.href.replace(/examples\/.*\/, '')
	* @previousNames path
	*/	
	var $_xinhaUrl=''; //trailing slash is not require
	var $_xinhaPath=''; //trailing slash is not require
	/**
	* path = '/my_images'
	* @previousNames 
	*/
	var $_imagesUrl;
	/**
	* full path = '/home/test/my_images'
	* @previousNames 
	*/
	var $_imagesDir;
	var $_baseDir;
	var $_baseUrl;
	var $_cssFileAddress='';
	var $_direction='ltr';
	/**
	* And the language we need to use in the editor.
	* @previousNames $lang
	*/
	var $_language='en';
	
	/**
	* list of editors all possible params
	*/
	var $_editorParams=array(
		'id'=>'',
		'templateName'=>'',
		'imagesUrl'=>'',
		'imagesDir'=>'',
		'baseUrl'=>'',
		'cssFileAddress'=>'',
		'direction'=>'',
		'loadJsTriggerId'=>'',
		'unloadJsTriggerId'=>'',
		'language'=>'',
		'loadOnDemand'=>''
	);

	var $_editors=array(
	); 


	var $_skins=array(
		'blueLook'=>'blue-look',
		'blueMetallic'=>'blue-metallic',
		'greenLook'=>'green-look',
		'inditreuse'=>'inditreuse',
		'titan'=>'titan',
		'xpBlue'=>'xp-blue',
		'xpGreen'=>'xp-green'
	);
	/**
	* @previousNames skin
	*/
	var $_skinName;

	var $_templates=array(
		'default'=>array(
			'plugins'=>array(
				'ImageManager'=>array()
			),
			'toolbar'=>array(),
			'showLoading'=>true
		),

		'full'=>array(
			'plugins'=>array(
				'CheckOnKeyPress'=>array(),
				'Abbreviation'=>array(),
				'BackgroundImage'=>array(),
				'CharacterMap'=>array(
					'mode'=>'panel'//popup, panel
				),
				'CharCounter'=>array(
					'showChar'=>true,
					'showWord'=>true,
					'showHtml'=>true
				),
				'ClientsideSpellcheck'=>array(),
				'ContextMenu'=>array(),
				'CSS'=>array(),
				'DefinitionList'=>array(),
				'DoubleClick'=>array(),
				'DynamicCSS'=>array(),
				'EditTag'=>array(),
				//'EnterParagraphs'=>array(), //due to the error
				'Equation'=>array(),       
				'ExtendedFileManager'=>array(),
				'Filter'=>array(),
				'FindReplace'=>array(),
				'FormOperations'=>array(),
				'Forms'=>array(),
				'FullPage'=>array(),
				'FullScreen'=>array(), //due to the error
				'GetHtml'=>array(),
				'HorizontalRule'=>array(),
				'HtmlEntities'=>array(),
				'HtmlTidy'=>array(),
				//'ImageManager'=>array(), due to the conflict with extended file manager
				'InsertAnchor'=>array(),
				'InsertMarquee'=>array(),
				'InsertPagebreak'=>array(),
				'InsertPicture'=>array(),
				'InsertSmiley'=>array(),
				'InsertSnippet'=>array(),
				'InsertWords'=>array(),
				'LangMarks'=>array(),
				'Linker'=>array(),//dute to the error in IE
				'ListType'=>array(
					'mode'=>'toolbar'//toolbar, panel
				),
				'NoteServer'=>array(),
				'PasteText'=>array(),
				'QuickTag'=>array(),
				'SaveSubmit'=>array(),
				'SetId'=>array(),
				'SmartReplace'=>array(),
				'SpellChecker'=>array(),
				'Stylist'=>array(),
				'SuperClean'=>array(),
				'TableOperations'=>array(),
				'Template'=>array(),
				'UnFormat'=>array()
			),
			'toolbar'=>array(
				array("popupeditor"),
				array("separator","formatblock","fontname","fontsize","bold","italic","underline","strikethrough","separator","forecolor","hilitecolor","textindicator"),
				array("separator","subscript","superscript"),
				array("linebreak","separator","justifyleft","justifycenter","justifyright","justifyfull"),
				array("separator","insertorderedlist","insertunorderedlist","outdent","indent"),
				array("separator","inserthorizontalrule","createlink","insertimage","inserttable"),
				array("linebreak","separator","undo","redo","selectall","print"),
				array("cut","copy","paste","overwrite","saveas"),
				array("separator","killword","clearfonts","removeformat","toggleborders","splitblock","lefttoright","righttoleft"),
				array("separator","htmlmode","showhelp","about")
			),
			'fonts'=>array(
				'Arial'=>array('arial','helvetica','sans-serif'),
				'Courier New'=>array('courier new','courier','monospace'),
				'Georgia'=>array('georgia','times new roman','times','serif'),
				'Tahoma'=>array('tahoma','arial','helvetica','sans-serif'),
				'Times New Roman'=>array('times new roman','times,serif'),
				'Verdana'=>array('verdana','arial','helvetica','sans-serif'),
				'impact'=>array('impact'),
				'WingDings'=>array('wingdings')
			),
			'fontSizes'=>array(
			    '1 (8 pt)' => '1',
			    '2 (10 pt)'=> '2',
    			'3 (12 pt)'=> '3',
			    '4 (14 pt)'=> '4',
    			'5 (18 pt)'=> '5',
			    '6 (24 pt)'=> '6',
    			'7 (36 pt)'=> '7'
			), 
			'width'=>'auto',
			'height'=>'auto',
			'statusBar'=>true,
			'sizeIncludesBars'=>false,
			'sizeIncludesPanels'=>true,
			'showLoading'=>true,
			'htmlareaPaste'=>false,
			'mozParaHandler'=>'best',//best , built-in , dirty
			'getHtmlMethod'=>'DOMwalk', //DOMwalk,TransformInnerHTML
			'undoSteps'=>20,
			'undoTimeout'=>500,
			'changeJustifyWithDirection'=>false,
			'fullPage'=>false,
			//'pageStyle'=>'',
			//'baseHref'=>'',
			'expandRelativeUrl'=>true,
			'stripBaseHref'=>true,
			'stripSelfNamedAnchors'=>true,
			'only7BitPrintablesInURLs'=>true,
			'sevenBitClean'=>false,
			'killWordOnPaste'=>true,
			'makeLinkShowsTarget'=>true,
			'flowToolbars'=>true,
			'stripScripts'=>false
			//'pageStyleSheets'=>array('own.css')
		),
		
		'simple'=>array(
			'plugins'=>array(),
			'fonts'=>array(),
			'toolbar'=>array(
				array("bold","italic","underline","strikethrough"),
				array("separator","forecolor","textindicator"),
				array("separator","cut","copy","paste"),
				array("separator","createlink","clearfonts"),
				array("separator","justifyleft","justifycenter","justifyright","justifyfull"),
				array("separator","undo","redo"),
				array("separator","righttoleft","lefttoright")
			),
			'fontSizes'=>array(),
			'statusBar'=>true
		),
		
		'fullWidthFileAndImageManager'=>array(
			'plugins'=>array(
				'ExtendedFileManager'=>array()
			),
			'toolbar'=>array()
		),
		
		'simpleWithImageManager'=>array(
			'plugins'=>array(
				'ImageManager'=>array()
			),
			'fonts'=>array(),
			'fontSizes'=>array(),
			'toolbar'=>array(
				array("bold","italic","underline","strikethrough"),
				array("separator","forecolor","textindicator"),
				array("separator","cut","copy","paste"),
				array("separator","createlink","clearfonts"),
				array("separator","undo","redo"),
				array("separator","righttoleft","lefttoright"),
				array("separator","insertimage")
			),
			'statusBar'=>true
		),
		
		'articleWriting'=>array(
			'plugins'=>array(
				'ImageManager'=>array(),
				'CheckOnKeyPress'=>array(),
				'InsertAnchor'=>array(),
			),
			'fonts'=>array(),
			'fontSizes'=>array(),
			'toolbar'=>array(
                array("separator","cut","copy","paste"),
                array("separator","bold","italic","underline","strikethrough"),
                array("separator","justifyleft","justifycenter","justifyright","justifyfull"),
				array("separator","insertorderedlist","insertunorderedlist","outdent","indent"),
				array("separator","createlink","insertimage"),
				array("separator","undo","redo"),
				array("separator","righttoleft","lefttoright"),
                array("separator","htmlmode")
			),
			'statusBar'=>false
		),
	);
	
	var $_templateName='';
	
	var $_loadedPlugins=array();
	
	function __construct($options) {
		$this->setOptions($options);
	}
	
	/**
	* 
	*/
	function getTemplateInfo($templateName) {
		return $this->_templates[$this->_templateName];
	}

	/**
	* fill properties according to selected template
	*/
	function loadTemplate($templateName) {
		$this->_templateName=$templateName;
		$templateName=$this->getTemplateInfo($this->_templateName);
	}
	
	
	/**
	* 
	*/
	function setOptions($options,$merge=false) {
		if (isset($options['editors'])) {
			$value=$options['editors'];
			foreach ($value as $id=>$valueInfo) {
				foreach ($this->_editorParams as $paramName=>$__v) {
					if (!isset($valueInfo[$paramName])) {
						$valueInfo[$paramName]=$options[$paramName];
					}
					$value[$id][$paramName]=$valueInfo[$paramName];
				}
			}
			$options['editors']=$value;
		}
		if (isset($options['xinhaUrl'])) {
			$options['xinhaUrl']=cmfcUrl::normalize($options['xinhaUrl']);
		}
		if (isset($options['xinhaPath'])) {
			$options['xinhaPath']=cmfcDirectory::normalizePath($options['xinhaPath']);
		}
		
		$r=parent::setOptions($options,$merge); 
		return $r;
	}
	
	
	function loadCore() {?>
		<script type="text/javascript">
			_editor_url='<?php echo $this->_xinhaUrl?>/';
			_editor_lang ='<?php echo $this->_language?>';
			_editor_skin='<?php echo $this->_skins[$this->_skinName]?>';
			xinhaEditorsWrappers=new Array();
		</script>
		<!-- Load up the actual editor core -->
		<script type="text/javascript" src="<?php echo $this->_xinhaUrl.'/'?>XinhaCore.js"></script>
		
        <script type="text/javascript" language="javascript">
			function <?php echo $this->_prefix?>EditorWrapper() {
				this.id;
				this.instance;
				this.xinhaConfig=null;
				this.xinhaPlugins=null;
				this.loadJsTriggerId=null;
				this.unloadJsTriggerId=null;
				this.loadOnDemand=false;
				this.pluginsLoaded=false;
				this.textareaHtml;
				this.textareaClone;
				this.started=false;
				var _this=this;
			}
				
			<?php echo $this->_prefix?>EditorWrapper.prototype.preparePlugins=function() {
			};
			
			<?php echo $this->_prefix?>EditorWrapper.prototype.prepareConfig=function() {
			};
			
			<?php echo $this->_prefix?>EditorWrapper.prototype.unload=function () {
				if (this.started==true) {
  					var iframe=document.getElementById('XinhaIFrame_'+this.id);
					var textarea=document.getElementById(this.id);
					var table=iframe.parentNode.parentNode.parentNode.parentNode;
					
					textarea.setAttribute('_xinha_dom0Events','');
					textarea.parentNode.removeChild(textarea);
					
					table.parentNode.insertBefore(this.textareaClone,table);
					table.style.display='none';
					
					this.instance=null;
					
					iframe.parentNode.removeChild(iframe);
					//if (!Xinha.is_ie)
					table.parentNode.removeChild(table);
					this.started=false;
				}
			}
			
			<?php echo $this->_prefix?>EditorWrapper.prototype.initialize=function() {
				if (this.instance==null) {
					<?php
					/** STEP 2 ***************************************************************
					* Now, what are the names of the textareas you will be turning into
					* editors?
					************************************************************************/
					?>
					var xinhaEditors = [ this.id ];
					
					<?php
					/** STEP 3 ***************************************************************
					* We first create editors for the textareas.
					*
					* You can do this in two ways, either
					*
					*   xinhaEditors   = Xinha.makeEditors(xinhaEditors, xinhaConfig, xinhaPlugins);
					*
					* if you want all the editor objects to use the same set of plugins, OR;
					*
					*   xinhaEditors = Xinha.makeEditors(xinhaEditors, xinhaConfig);
					*   xinhaEditors['myTextArea'].registerPlugins(['Stylist','FullScreen']);
					*   xinhaEditors['anotherOne'].registerPlugins(['CSS','SuperClean']);
					*
					* if you want to use a different set of plugins for one or more of the
					* editors.
					************************************************************************/
					?>
					
				    if (this.xinhaConfig==null) {
				        this.xinhaConfig=this.prepareConfig();
					}
					
					xinhaEditors = Xinha.makeEditors(xinhaEditors, this.xinhaConfig, this.xinhaPlugins);
					this.instance=xinhaEditors[this.id];
					
					if (document.getElementById(this.id))
						this.textareaClone=document.getElementById(this.id).cloneNode(true);
					
					return true;
				}
				return true;
			}
			
			<?php echo $this->_prefix?>EditorWrapper.prototype.prepare=function() {

				if (this.loadOnDemand) {
					
					if (this.loadJsTriggerId==null) {
						var a=document.createElement('a');
						var a2=document.createElement('a');
						var br=document.createElement('br');
						var br2=document.createElement('br');
						a.href="javascript:xinhaEditorsWrappers['"+this.id+"'].load();";
						a.innerHTML='load it';
						//a.onClick="return false";
						//setAttribute
					
						a2.href="javascript:xinhaEditorsWrappers['"+this.id+"'].unload();";
						a2.innerHTML='/unload it';
						var textarea=document.getElementById(this.id);
						textarea.parentNode.insertBefore(br,textarea);
						textarea.parentNode.insertBefore(a,textarea);
						textarea.parentNode.insertBefore(a2,textarea);
						textarea.parentNode.insertBefore(br2,textarea);
					} else {
						var a;
						a=document.getElementById(this.loadJsTriggerId);
                        
						if (a.tagName=='A')
							a.href="javascript:xinhaEditorsWrappers['"+this.id+"'].load();";
						else
							this.attachEvent(a,'click',new Function("xinhaEditorsWrappers['"+this.id+"'].load();"));
						
						a=document.getElementById(this.unloadJsTriggerId);
						if (a.tagName=='A')
							a.href="javascript:xinhaEditorsWrappers['"+this.id+"'].load();";
						else
							this.attachEvent(a,'click',new Function("xinhaEditorsWrappers['"+this.id+"'].unload();"));
					}

				} else {
					this.load();
				}
			}
			
			<?php echo $this->_prefix?>EditorWrapper.prototype.load=function() {
				if (!this.started) {
					this.initialize();
					<?php
					/** STEP 5 ***************************************************************
					* Finally we "start" the editors, this turns the textareas into
					* Xinha editors.
					************************************************************************/
					?>
					Xinha.startEditors([this.instance]);
					this.started=true;
				}
			}
			
			<?php echo $this->_prefix?>EditorWrapper.prototype.fixHeightBugInIe=function(xinhaEditors) {
				
				var editorId;
				var xinhaEditor;
				if (xinhaEditors) {
					for (editorId in xinhaEditors) {
						xinhaEditor=xinhaEditors[editorId];

						if (document.all) {
							xinhaEditor._textArea.style.height=xinhaEditor._textArea.offsetHeight+40;
							xinhaEditor.height=xinhaEditor._textArea.style.height;
							//alert(xinhaEditor._textArea.rows);
							//xinhaEditor._textArea.rows=xinhaEditor._textArea.rows+20;
							//xinhaEditor.sizeEditor(xinhaEditor._textArea.offsetWidth,xinhaEditor._textArea.offsetHeight+80);
						} else {
						}
					}
				}
			}
			
			<?php echo $this->_prefix?>EditorWrapper.prototype.unfreeze=function() {
				this.instance.sizeEditor();
			}
			
			
			<?php echo $this->_prefix?>EditorWrapper.prototype.attachEvent=function(obj,eventName,func) {
				if (obj.addEventListener) {
					obj.addEventListener(eventName, func , false);
				} else if (obj.attachEvent) {
					obj.attachEvent('on'+eventName, func);
				}
			}						
	</script>
	
	
    <script type="text/javascript" language="javascript">
        function CheckOnKeyPress(editor) {
            this.editor = editor;
        }

    	CheckOnKeyPress._pluginInfo = {
            name          : "CheckOnKeyPress",
            version       : "1.0",
            developer     : "",
            developer_url : "",
            c_owner       : "Niko Sams",
            sponsor       : "",
            sponsor_url   : "",
            license       : "htmlArea"
        };

        CheckOnKeyPress.prototype.onKeyPress = function(ev) {                               
            <?php /*
			alert(ev.which);
            formChanged(); // My change handler for the whole form
            */?>
        }
        </script>
	<?php
	}
	
	function arrayKeysToJavascript($arr) {
		//echo 'checkMeWithMe';
		//var_dump(implode(',',array_keys($arr)));
		
		if (is_array($arr))
			if (!empty($arr)) {
				$str='';
				foreach ($arr as $key=>$v) {
					$str.=$comma."'".$key."'";
					$comma=',';
					$b[]=$key;
				}
				
				//return cmfcHtml::phpToJavascript($b,0,true);
				return $str;
			}
	}
	
	function prepareEditors() {
		foreach ($this->_editors as $id=>$info)
			$this->prepareEditor($id);
	}
		
	function prepareEditor($id) {
		$editorInfo=$this->_editors[$id];
		$templateInfo=$this->_templates[$editorInfo['templateName']];
		if (empty($editorInfo['cssFileAddress'])) {
			$editorInfo['cssFileAddress']=$this->_cssFileAddress;
		}
	?>
		<script type="text/javascript" language="javascript">
			xinhaEditorsWrappers['<?php echo $id?>']=new <?php echo $this->_prefix?>EditorWrapper();
			xinhaEditorsWrappers['<?php echo $id?>'].id='<?php echo $id?>';
			xinhaEditorsWrappers['<?php echo $id?>'].loadOnDemand=<?php echo cmfcHtml::phpToJavascript($editorInfo['loadOnDemand'],0,true)?>;
			xinhaEditorsWrappers['<?php echo $id?>'].loadJsTriggerId=<?php echo cmfcHtml::phpToJavascript($editorInfo['loadJsTriggerId'],0,true)?>;
			xinhaEditorsWrappers['<?php echo $id?>'].unloadJsTriggerId=<?php echo cmfcHtml::phpToJavascript($editorInfo['unloadJsTriggerId'],0,true)?>;
			
			xinhaEditorsWrappers['<?php echo $id?>'].prepareConfig=function() {
				var xinhaConfig=null;

				<?
				/** STEP 3 ***************************************************************
				* We create a default configuration to be used by all the editors.
				* If you wish to configure some of the editors differently this will be
				* done in step 4.
				*
				* If you want to modify the default config you might do something like this.
				*
				*   xinhaConfig = new Xinha.Config();
				*   xinhaConfig.width  = 640;
				*   xinhaConfig.height = 420;
				*
				*************************************************************************/
				?>
					
				xinhaConfig = new Xinha.Config();
				xinhaConfig.sizeIncludesBars=<?php echo ($templateInfo['sizeIncludesBars'])?'true':'false'?>;
				xinhaConfig.showLoading=<?php echo ($templateInfo['showLoading'])?'true':'false'?>;
				xinhaConfig.statusBar=<?php echo ($templateInfo['statusBar'])?'true':'false'?>;
				<?php if (!isset($templateInfo['expandRelativeUrl'])) $templateInfo['expandRelativeUrl']=true; ?>
				xinhaConfig.expandRelativeUrl=<?php echo ($templateInfo['expandRelativeUrl'])?'true':'false'?>;
				<?php if (!isset($templateInfo['stripBaseHref'])) $templateInfo['stripBaseHref']=true; ?>
				xinhaConfig.stripBaseHref=<?php echo ($templateInfo['stripBaseHref'])?'true':'false'?>;
				
				<?php if (isset($templateInfo['plugins']['ImageManager'])) {?>
				
				with(xinhaConfig.ImageManager){
					<?php
					$map=array(
						'baseDir' => 'base_dir',
						'baseUrl' => 'base_url',
						'imagesDir' => 'images_dir',
						'imagesUrl' => 'images_url',
						'safeMode' => 'safe_mode',
						'thumbnailPrefix' => 'thumbnail_prefix',
						'thumbnailDir' => 'thumbnail_dir',
						'resizedPrefix' => 'resized_prefix',
						'resizedDir' => 'resized_dir',
						'showFullOptions' => 'show_full_options',
						'allowNewDir' => 'allow_new_dir',
						'allowUpload' => 'allow_upload',
						'validateImages' => 'validate_images',
						'defaultThumbnail' => 'default_thumbnail',
						'thumbnailWidth' => 'thumbnail_width',
						'thumbnailHeight' => 'thumbnail_height',
						'tmpPrefix' => 'tmp_prefix',
						'viewMode' => 'ViewMode', //details, thumbs
					);
					
					$parameters=array();
					 
					if (isset($editorInfo['imagesDir']))
						$parameters[$map['imagesDir']]=$editorInfo['imagesDir'];
					if (isset($editorInfo['imagesUrl']))
						$parameters[$map['imagesUrl']]=$editorInfo['imagesUrl'];
					foreach ($templateInfo['plugins']['ImageManager'] as $key=>$value) {
						if (isset($map[$key])) {
							$parameters[$map[$key]]=$value;
						}
					}
					
					require_once($this->_xinhaPath.'/contrib/php-xinha.php');
					xinha_pass_to_php_backend($parameters); 
				?>
				}
				<?php }?> 
					
				<?php if (isset($templateInfo['plugins']['ExtendedFileManager'])) {?>
				with(xinhaConfig.ExtendedFileManager){
					<?php
					$map=array(
						'baseDir' => 'base_dir',
						'baseUrl' => 'base_url',
						'imagesDir' => 'images_dir',
						'imagesUrl' => 'images_url',
						'dateFormat'=>'date_format',
						'safeMode' => 'safe_mode',
						'imgLibrary'=>'img_library',
						'viewType' => 'view_type', //thumbview , listview
						'thumbnailPrefix' => 'thumbnail_prefix',
						'thumbnailDir' => 'thumbnail_dir',
						'resizedPrefix' => 'resized_prefix',
						'resizedDir' => 'resized_dir',
						'showFullOptions' => 'show_full_options',
						'allowNewDir' => 'allow_new_dir',
						'allowEditImage'=>'allow_edit_image',
						'allowRename'=>'allow_rename',
						'allowCutCopyPaste'=>'allow_cut_copy_paste',
						'useColorPickers'=>'use_color_pickers',
						'imagesEnableAlt'=>'images_enable_alt',
						'imagesEnableTitle'=>'images_enable_title',
						'imagesEnableAlign'=>'images_enable_align',
						'imagesEnableStyling'=>'images_enable_styling',
						'linkEnableTarget'=>'link_enable_target',
						'allowUpload' => 'allow_upload',
						'maxFileSizeKbImage' => 'max_filesize_kb_image',
						'maxFileSizeKbLink' => 'max_filesize_kb_link',
						'maxFolderSizeMb' => 'max_foldersize_mb', 
						'allowedImageExtensions' => 'allowed_image_extensions',
						'allowedLinkExtensions' => 'allowed_link_extensions',
						'defaultThumbnail' => 'default_thumbnail',
						'defaultListIcon' => 'default_listicon',
						'thumbnailExtensions' => 'thumbnail_extensions',
						'thumbnailWidth' => 'thumbnail_width',
						'thumbnailHeight' => 'thumbnail_height',						
						'tmpPrefix' => 'tmp_prefix',
						'validateImages' => 'validate_images',
						'filesDir' => 'files_dir',
						'filesUrl' => 'files_url',
					);
					if (!isset($editorInfo['allowedLinkExtensions'])) {
						$editorInfo['allowedLinkExtensions']=array("jpg","gif","pdf","ip","txt","psd","png","html","swf","xml","xls","gzip","tar","gz","rar","zip","pdf","docx","doc","ppt","pptx");
					}

					$parameters=array();
					
					if (isset($editorInfo['allowedLinkExtensions']))
						$parameters[$map['allowedLinkExtensions']]=$editorInfo['allowedLinkExtensions'];
					if (isset($editorInfo['imagesDir']))
						$parameters[$map['imagesDir']]=$editorInfo['imagesDir'];
					if (isset($editorInfo['imagesUrl']))
						$parameters[$map['imagesUrl']]=$editorInfo['imagesUrl'];

					foreach ($templateInfo['plugins']['ExtendedFileManager'] as $key=>$value) {
						if (isset($map[$key])) {
							$parameters[$map[$key]]=$value;
						}
					}

					require_once($this->_xinhaPath.'/contrib/php-xinha.php');
					xinha_pass_to_php_backend($parameters);
				?>
				}
				<?php } 
					
				/**
				* removing some buttons :
				*/
				 if (!empty($templateInfo['buttonsToHide']) and 1==0) {?>
					xinhaConfig.hideSomeButtons(<?php echo $this->arrayKeysToJavascript($templateInfo['buttonsToHide'])?>);
				<?php }
				/**
				* toolbar
				*/
				if (!empty($templateInfo['toolbar'])) { 
					$comma='';
				?>            
					xinhaConfig.toolbar =
					[
						<?php foreach ($templateInfo['toolbar'] as $panel) { ?>
							<?php echo $comma?> <?php echo cmfcHtml::phpToJavascript($panel,0,true)?>
						<?php $comma=',';}?>
					];
				<?php }?>
					
					
				<?php if (!empty($templateInfo['fonts'])) {
					$comma='';
				?>
					xinhaConfig.fontname = {
						"&mdash; font &mdash;" : '',
						<?php foreach ($templateInfo['fonts'] as $fontGroup=>$fontNames) {?>
							<?php echo $comma?> "<?php echo $fontGroup?>" : <?php echo cmfcHtml::phpToJavascript($fontNames,0,true)?>
						<?php 
							$comma="\n,";
						}?>
					};
				<?php }?>
					
					
				<?php if (!empty($templateInfo['fontSizes'])) {
					$comma='';
				?>
					xinhaConfig.fontsize = {
						"&mdash; font &mdash;" : '',
						<?php foreach ($templateInfo['fontSizes'] as $fontSizeName=>$fontSize) {?>
							<?php echo $comma?> "<?php echo $fontSizeName?>" : '<?php echo $fontSize?>'
						<?php 
							$comma=',';
						}?>
					};
				<?php }?>
					
	<?php
				/*
				// We can load an external stylesheet like this - NOTE : YOU MUST GIVE AN ABSOLUTE URL
				//  otherwise it won't work!
				xinhaConfig.stylistLoadStylesheet(document.location.href.replace(/[^\/]*\.html/, 'stylist.css'));
				
				// Or we can load styles directly
				xinhaConfig.stylistLoadStyles('p.red_text { color:red }');
				
				// If you want to provide "friendly" names you can do so like
				// (you can do this for stylistLoadStylesheet as well)
				xinhaConfig.stylistLoadStyles('p.pink_text { color:pink }', {'p.pink_text' : 'Pretty Pink'});
				*/
	?>
				
				<?php if (file_exists($editorInfo['cssFileAddress']) or 1==1) {?>
					//document.location.href.replace(/[^\/]*\.html/, 'stylist.css')
	<?php /*
					//xinhaConfig.stylistLoadStylesheet='<?php echo $this->_cssFileAddress?>';
					//xinhaConfig.pageStyle = "body { font-family: Arabic; direction: rtl; }";
					*/
	?>
					xinhaConfig.pageStyle = "@import url('<?php echo $editorInfo['cssFileAddress']?>');";
				<?php }?>
				
				<?php if (!empty($editorInfo['direction'])) {?>
					xinhaConfig.pageStyle += " body { direction: <?php echo $editorInfo['direction']?>; }";
				<?php }?>
				
				this.xinhaConfig=xinhaConfig;
				
				return xinhaConfig;
			};
			
		    xinhaEditorsWrappers['<?php echo $id?>'].preparePlugins=function () {

				var xinhaPlugins=null;
				<?php
				/** STEP 1 ***************************************************************
				* First, what are the plugins you will be using in the editors on this
				* page.  List all the plugins you will need, even if not all the editors
				* will use all the plugins.
				************************************************************************/
				?>
				<?php
				$plugins=$templateInfo['plugins'];
				/*
				if (is_array($plugins))
				foreach ($plugins as $key=>$plugin) {
					if (!in_array($key,$this->_loadedPlugins))
						$this->_loadedPlugins[]=$key;
					else
						unset($plugins[$key]);
				}
                */
				?> 
				
				xinhaPlugins = [ <?php echo $this->arrayKeysToJavascript($plugins)?> ];
				
				this.xinhaPlugins=xinhaPlugins;
				
				// THIS BIT OF JAVASCRIPT LOADS THE PLUGINS, NO TOUCHING  :)
				var _this=this;
				
				//Xinha.loadPlugins(xinhaPlugins,function() {_this.pluginsLoaded=true});
				
				Xinha.loadPlugins(xinhaPlugins, this.prepareConfig);
				
				return xinhaPlugins;
			}
			xinhaEditorsWrappers['<?php echo $id?>'].preparePlugins();
		</script>
	<?php }
	
	
	function changeEditorConfig($id,$param,$value,$append=false) {?>
		xinhaEditors['<?php echo $id?>'].instance.config.<?php echo $param?><?php echo ($append)?'+':''?>=<?php echo $value?>;
	<?php
	}
	
	
	function isEmpty($string) {
		
	}


	function loadOnPageLoad() {?>
		<script type="text/javascript">
			var initAll=function() {
				for (i in xinhaEditorsWrappers) {
					if (typeof(xinhaEditorsWrappers[i])=='object') {
						xinhaEditorsWrappers[i].prepare();
					}
				}
			}
			
			if (window.addEventListener) {
				window.addEventListener("load", initAll , false);
			} else if (window.attachEvent) {
				window.attachEvent("onload", initAll);
			}
			window.onunload = HTMLArea.collectGarbageForIE;
		</script>
	<?php
	}
}
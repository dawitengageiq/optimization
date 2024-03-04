<div role="tabpanel" class="tab-pane {{$highPayingContentActive}}" id="high_paying_content_tab">
	{{--<label for="for_content" style="padding-top:7px" id="">Campaign contents texts</label> @{{ type }}--}}
	{{--<hr>--}}
	{{--<form id="hpcs-form">--}}
		{{--<div class="row">--}}
			{{--<div class="col-md-12">--}}
				{{--<div class="row form-group">--}}
					{{--<div class="col-md-6">--}}
						{{--<label for="">Campaign Name</label>--}}
						{{--<input type="text" name="name" class="form-control" v-model="contents.name">--}}
					{{--</div>--}}

					{{--<div class="col-md-6">--}}
						{{--<label for="">Deal</label>--}}
						{{--<input type="text" name="deal" class="form-control" v-model="contents.deal">--}}
					{{--</div>--}}
				{{--</div>--}}

				{{--<div class="row form-group">--}}
					{{--<div class="col-md-6">--}}
						{{--<label for="">Description</label>--}}
						{{--<textarea name="description" class="form-control" rows="3" v-model="contents.description"></textarea>--}}
					{{--</div>--}}

					{{--<div class="col-md-6">--}}
						{{--<div class="row form-group">--}}
							{{--<div class="col-md-12">--}}
								{{--<label for="">Sticker Url</label>--}}
								{{--<input type="text" name="sticker" class="form-control" v-model="contents.sticker">--}}
							{{--</div>--}}
						{{--</div>--}}

						{{--<div class="row form-group" v-if="type=='external'">--}}
							{{--<div class="col-md-12">--}}
								{{--<label for="">CPA Creative Id</label>--}}
								{{--<input type="text" name="cpa_creative_id" class="form-control" v-model="contents.cpa_creative_id">--}}
							{{--</div>--}}
						{{--</div>--}}
					{{--</div>--}}
				{{--</div>--}}
			{{--</div>--}}

			{{--<div class="col-md-12" v-if="type=='coreg'">--}}
				{{--<hr>--}}
				{{--<div class="form-group row">--}}
					{{--<div class="col-md-5">--}}
						{{--<div class="row">--}}
							{{--<div class="col-md-12">--}}
								{{--<label for="">Add Required Fields</label>--}}
								{{--<select name="field" v-model="requiredField" @change="addRequiredField" class="form-control">--}}
									{{--<option value="0" selected>-Choose here-</option>--}}
									{{--<optgroup v-for="(groups,gi) in fields" :label="groups.name">--}}
										{{--<option v-for="(field,fi) in groups.values" :value="[gi,fi]">@{{ field.field }} @{{ field.val }} @{{ field.format ? '['+field.format+']' : ''  }}</option>--}}
									{{--</optgroup>--}}
								{{--</select>--}}
								{{--<hr>--}}
								{{--<div class="row" v-show="requiredField[0]==5 && requiredField[1]==0">--}}
									{{--<div class="col-md-5">--}}
										{{--<label for="">Custom Field</label>--}}
										{{--<input type="text" v-model="customs.field" class="form-control">--}}
									{{--</div>--}}
									{{--<div class="col-md-5">--}}
										{{--<label for="">Custom Value</label>--}}
										{{--<input type="text" v-model="customs.value" class="form-control">--}}
									{{--</div>--}}

									{{--<div class="col-md-2">--}}
										{{--<label for=""> </label>--}}
										{{--<button type="button" class="btn btn-primary" @click="insertCustoms">Insert</button>--}}
									{{--</div>--}}
								{{--</div>--}}
							{{--</div>--}}
						{{--</div>--}}
					{{--</div>--}}

					{{--<div class="col-md-7">--}}
						{{--<label for="">Required Fields</label>--}}
						{{--<ol>--}}
							{{--<li v-for="(field,index) in selectedRequiredField" class="item-fields" :key="index">--}}
								{{--<input type="text" class="" style="width: 35%; padding: 5px;height: 30px; border-radius: 3px" v-model="field.field">---}}
								{{--@{{ field.val }} @{{ field.format ? '['+field.format+']' : ''  }} @{{ field.s==1 ? '[STATIC]' : '' }}--}}
								{{--<span class="fa fa-remove pull-right" style="color: red;" @click="selectedRequiredField.splice(index,1)"></span>--}}
							{{--</li>--}}
						{{--</ol>--}}
					{{--</div>--}}
				{{--</div>--}}
				{{--<hr>--}}
				{{--<div class="form-group row">--}}
					{{--<div class="col-md-5">--}}
						{{--<div class="row">--}}

							{{--<div class="col-md-12 form-group">--}}
								{{--<label for="">Question / Label</label>--}}
								{{--<textarea class="form-control" rows="3" v-model="additionalField.question"></textarea>--}}
							{{--</div>--}}

							{{--<div class="col-md-12 form-group">--}}
								{{--<label for="">Field (parameter)</label>--}}
								{{--<input type="text" class="form-control" v-model="additionalField.field">--}}
							{{--</div>--}}

							{{--<div class="col-md-12 form-group">--}}
								{{--<label for="">Field Types</label>--}}
								{{--<select name="type" class="form-control" v-model="additionalField.type">--}}
									{{--<option value="0">-Select Type-</option>--}}
									{{--<option value="input">Input Field</option>--}}
									{{--<option value="textarea">Text Area Field</option>--}}
									{{--<option value="select">Selection Field</option>--}}
									{{--<option value="yesOrNo">Selection Field (Yes ?? No only)</option>--}}
									{{--<option value="checkbox">Checkbox with consent</option>--}}
									{{--<option value="checkbox_no">No checkbox with consent</option>--}}
									{{--<option value="telephone">Phone Number</option>--}}
									{{--<option value="mobile-phone">Mobile Phone</option>--}}
									{{--<option value="phone-3">Phone Number(3 fields)</option>--}}
									{{--<option value="mobile-phone-3">Mobile Phone (3 fields)</option>--}}
								{{--</select>--}}
							{{--</div>--}}

							{{--<div class="col-md-12 form-group" v-show="additionalField.type=='select'">--}}
								{{--<label for="">Set Select Field</label>--}}
								{{--<div class="row">--}}
									{{--<div class="col-md-2">--}}
										{{--<label>Opt</label>--}}
									{{--</div>--}}
									{{--<div class="col-md-4">--}}
										{{--<label>Field</label>--}}
									{{--</div>--}}
									{{--<div class="col-md-4">--}}
										{{--<label>Value</label>--}}
									{{--</div>--}}
									{{--<div class="col-md-2">--}}
										{{--<label>Act</label>--}}
									{{--</div>--}}
								{{--</div>--}}
								{{--<div class="row">--}}
									{{--<div class="col-md-2">--}}
										{{--<input type="checkbox" @click="chooseSelections.mirror = !chooseSelections.mirror">Mirror--}}
									{{--</div>--}}
									{{--<div class="col-md-4">--}}
										{{--<input type="text" class="form-control" v-model="chooseSelections.name" @keyup="selectionSyncer(1)">--}}
									{{--</div>--}}
									{{--<div class="col-md-4">--}}
										{{--<input type="text" class="form-control" v-model="chooseSelections.value" @keyup="selectionSyncer(2)">--}}
									{{--</div>--}}
									{{--<div class="col-md-2">--}}
										{{--<button @click="addChooseSelections" class="btn btn-primary" type="button"><i class="fa fa-plus" aria-hidden="true"></i></button>--}}
									{{--</div>--}}
								{{--</div>--}}

								{{--<div class="row" v-for="(options,index) in chooseSelections.options">--}}
									{{--<div class="col-md-2">--}}
										{{--<p class="pull-left">@{{ index+1 }}</p>--}}
									{{--</div>--}}
									{{--<div class="col-md-4">--}}
										{{--<input type="text" class="form-control" v-model="options.name">--}}
									{{--</div>--}}
									{{--<div class="col-md-4">--}}
										{{--<input type="text" class="form-control" v-model="options.value">--}}
									{{--</div>--}}
									{{--<div class="col-md-2">--}}
										{{--<span class="fa fa-remove pull-right " style="color: red;" @click="chooseSelections.options.splice(index,1)"></span>--}}
									{{--</div>--}}
								{{--</div>--}}
							{{--</div>--}}

							{{--<div class="col-md-12 form-group">--}}
								{{--<button class="btn btn-primary" type="button" @click="addAdditonalField"> Set Additional Field</button>--}}
							{{--</div>--}}

						{{--</div>--}}

					{{--</div>--}}

					{{--<div class="col-md-7">--}}
						{{--<label for="">Additional Fields</label>--}}
						{{--<ol>--}}
							{{--<li v-for="(field,index) in selectedAdditionalFields" class="item-fields">--}}
								{{--<div class="row">--}}
									{{--<div class="col-md-3">--}}
										{{--<label>Field Type</label>--}}
									{{--</div>--}}
									{{--<div class="col-md-8">--}}
										{{--@{{ field.type }}--}}
									{{--</div>--}}
									{{--<div class="col-md-1">--}}
										{{--<span class="fa fa-remove pull-right " style="color: red;" @click="selectedAdditionalFields.splice(index,1)"></span>--}}
									{{--</div>--}}
								{{--</div>--}}
								{{--<div class="row">--}}
									{{--<div class="col-md-3">--}}
										{{--<label>Parameter</label>--}}
									{{--</div>--}}
									{{--<div class="col-md-9">--}}
										{{--@{{ field.field }}--}}
									{{--</div>--}}
								{{--</div>--}}
								{{--<div class="row">--}}
									{{--<div class="col-md-3">--}}
										{{--<label>Question</label>--}}
									{{--</div>--}}
									{{--<div class="col-md-9">--}}
										{{--@{{ field.question }}--}}
									{{--</div>--}}
								{{--</div>--}}
								{{--<div class="row" v-show="field.type=='yesOrNo' || field.type=='select'">--}}
									{{--<div class="col-md-3">--}}
										{{--<label>Options</label>--}}
									{{--</div>--}}
									{{--<div class="col-md-9">--}}
										{{--<div v-if="field.type=='yesOrNo' || field.type=='select'">--}}
											{{--<select>--}}
												{{--<option v-for="option in field.options">Name : @{{ option.name }} - Value : @{{ option.value }}</option>--}}
											{{--</select>--}}
										{{--</div>--}}
									{{--</div>--}}
								{{--</div>--}}
							{{--</li>--}}
						{{--</ol>--}}
					{{--</div>--}}
				{{--</div>--}}

				{{--<hr>--}}

				{{--<div class="row form-group">--}}
					{{--<div class="col-md-12">--}}
						{{--<button class="btn btn-primary pull-right" type="button" @click="updateCampaignContents" :disabled="loading">--}}
						{{--<span v-if="loading"><i class="fa fa-spin fa-spinner" aria-hidden="true"></i></span>--}}
						{{--<span v-else>Save</span>--}}
						{{--</button>--}}
					{{--</div>--}}
				{{--</div>--}}


			{{--</div>--}}
		{{--</div>--}}


	{{--</form>--}}
</div>





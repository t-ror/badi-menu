<template>
	<div :key="componentKey">
		<Transition name="fade">
			<div v-if="loading" class="row loadingio-spinner-eclipse-wrapper">
				<div class="loadingio-spinner-eclipse-soruzlkidf"><div class="ldio-fs278rfv31">
					<div></div>
				</div></div>
			</div>
		</Transition>
		<fieldset :disabled="actionsDisabled">
			<div v-html="creatFormHtml" ref="createForm" v-on:submit.prevent="onCreateFormSubmit"></div>
		</fieldset>
		<div>
			<table>
				<template v-for="(mealTag, index) in sortedMealTags">
					<tr class="py-2">
						<td class="px-2 py-1"><span class="badge bg-primary">{{ mealTag.name }}</span></td>
						<td class="px-2 py-1">
							<i v-if="mealTag.usage === 0">použito: {{ mealTag.usage }}</i>
							<a v-if="mealTag.usage !== 0" :href="mealTag.showMealsUrl">
								<i>použito: {{ mealTag.usage }}</i>
							</a>
						</td>
						<td class="px-2 py-1">
							<a :class="'btn btn-primary ' + actionsDisabledClass" data-bs-toggle="collapse" :href="'#edit-form-' + mealTag.id" role="button" aria-expanded="false" :aria-controls="'edit-form-' + mealTag.id" title="upravit"><i class="fa fa-pen"></i></a> <a @click.prevent="deleteMealTag($event, mealTag.deleteUrl, index, mealTag.name)" :href="mealTag.deleteUrl" :class="'btn btn-danger ' + actionsDisabledClass" title="smazat"><i class="fa fa-trash"></i></a>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<div class="collapse" :id="'edit-form-' + mealTag.id">
								<fieldset :disabled="actionsDisabled">
									<div v-html="mealTag.editFormHtml" :ref="'editForm' + mealTag.id" :id="'editForm' + mealTag.id" v-on:submit.prevent="onEditFormSubmit($event, index, mealTag.editUrl)"></div>
								</fieldset>
								<hr>
							</div>
						</td>
					</tr>
				</template>
			</table>
		</div>
	</div>
</template>

<script>
import axios from 'axios';
import {eventBus} from '../app';

export default {
	name: 'MealTagList',
	data: () => ({
		loading: true,
		creatFormHtml: '',
		mealTags: [],
		actionsDisabled: false,
		componentKey: 0,
	}),
	props: {
		provideListDataLink: {
			type: String,
		},
		processCreateFormLink: {
			type: String,
		},
	},
	methods: {
		onCreateFormSubmit (event) {
			this.actionsDisabled = true
			let formData = new FormData(event.target)
			axios.post(this.processCreateFormLink, formData).then((response) => {
				if (response.data.mealTag !== undefined) {
					this.mealTags.push(response.data.mealTag)
					this.$refs.createForm.firstChild.reset()
				}

				eventBus.$emit('add-flash', response.data.flash.type, response.data.flash.message)
				this.actionsDisabled = false
			})
		},
		onEditFormSubmit (event, mealTagIndex, mealTagEditUrl) {
			this.actionsDisabled = true
			let formData = new FormData(event.target)
			axios.post(mealTagEditUrl, formData).then((response) => {
				if (response.data.mealTag !== undefined) {
					this.mealTags[mealTagIndex].name = response.data.mealTag.name
				} else if (response.data.status !== undefined && response.data.status === 'deleted') {
					eventBus.$emit('add-flash', response.data.flash.type, response.data.flash.message)
					this.mealTags.splice(mealTagIndex, 1)
					this.forceRerender();
				}

				eventBus.$emit('add-flash', response.data.flash.type, response.data.flash.message)
				this.actionsDisabled = false
			})
		},
		deleteMealTag (event, mealTagDeleteUrl, mealTagIndex, mealTagName) {
			if (confirm('Opravud chce smazat štítek "' + mealTagName + '"')) {
				this.actionsDisabled = true
				axios.post(mealTagDeleteUrl).then((response) => {
					if (response.data !== 'empty') {
						eventBus.$emit('add-flash', response.data.flash.type, response.data.flash.message)
					}

					this.mealTags.splice(mealTagIndex, 1)
					this.actionsDisabled = false
					this.forceRerender();
				})
			}
		},
		forceRerender() {
			this.componentKey += 1;
		}

	},
	computed: {
		sortedMealTags () {
			function compare(a, b) {
				if (a.name < b.name)
					return -1;
				if (a.name > b.name)
					return 1;
				return 0;
			}

			return this.mealTags.sort(compare);
		},
		actionsDisabledClass () {
			return this.actionsDisabled ? 'disabled' : ''
		}
	},
	async mounted () {
		let data = await axios.get(this.provideListDataLink)
		this.loading = false;
		this.creatFormHtml = data.data.createForm
		this.mealTags = data.data.mealTags
	},
}
</script>

<style scoped>
.fade-enter-active{
	transition: opacity 0.5s ease;
}
.fade-leave-active {
	transition: opacity 0.4s ease;
}

.fade-enter-from{
	opacity: 0;
}
.fade-leave-to {
	opacity: 0;
}
</style>
<template>
	<div>
		<div v-for="flash in flashes" :class="'alert alert-' + flash.type + ' alert-dismissible fade show app-flash-box'" role="alert">
			{{ flash.message }}
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	</div>
</template>

<script>
import {eventBus} from '../app';

export default {
	name: 'Flashes',
	data: () => ({
		flashes: [],
	}),
	methods: {
		addFlash (type, message) {
			this.flashes.push({'type': type, 'message': message})
		}
	},
	created: function() {
		eventBus.$on('add-flash', (type, message) => {
			this.addFlash(type, message)
		});
	},
}
</script>
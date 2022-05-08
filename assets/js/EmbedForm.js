export default class EmbedForm {
    static init () {
        document
            .querySelectorAll('.add_item_link')
            .forEach(btn => {
                btn.addEventListener("click", function (e) {
                    const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);
                    console.log(collectionHolder);

                    const item = document.createElement('div');
                    item.className = 'row mb-2';

                    item.innerHTML = collectionHolder
                        .dataset
                        .prototype
                        .replace(
                            /__name__/g,
                            collectionHolder.dataset.index
                        );

                    collectionHolder.appendChild(item);
                    addTagFormDeleteLink(item);

                    collectionHolder.dataset.index++;
                })
            });

        const addTagFormDeleteLink = (item) => {
            const removeFormButton = document.createElement('div');
            removeFormButton.className = 'col-2';
            removeFormButton.innerHTML = '<button type="button" class="delete_item_link btn btn-danger" style="margin-top: 2em;" data-collection-holder-class="mealIngredients"><span class="fa fa-times"></span></button>';

            item.append(removeFormButton);

            removeFormButton.addEventListener('click', (e) => {
                e.preventDefault();
                // remove the li for the tag form
                item.remove();
            });
        }

        document
            .querySelectorAll('div.app_embed-form div.row')
            .forEach((tag) => {
                addTagFormDeleteLink(tag)
            })
    }
}
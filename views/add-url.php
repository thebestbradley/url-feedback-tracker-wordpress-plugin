<div class="wrap">
    <h1>Add New URL</h1>
    <form method="post" action="">
        <?php wp_nonce_field('uft_add_url', 'uft_nonce'); ?>
        <input type="hidden" name="uft_action" value="add_url">
        <table class="form-table">
            <tr>
                <th><label for="url_name">URL Name</label></th>
                <td><input type="text" id="url_name" name="url_name" required></td>
            </tr>
            <tr>
                <th><label for="target_url">Target URL</label></th>
                <td><input type="url" id="target_url" name="target_url" required></td>
            </tr>
            <tr>
                <th><label for="property_id">Property</label></th>
                <td>
                    <?php
                    wp_dropdown_pages(array(
                        'post_type' => 'uft_property',
                        'name' => 'property_id',
                        'show_option_none' => 'Select a property',
                        'option_none_value' => '',
                        'required' => true
                    ));
                    ?>
                </td>
            </tr>
        </table>
        <?php submit_button('Add URL'); ?>
    </form>
</div>

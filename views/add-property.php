<div class="wrap">
    <h1>Add New Property</h1>
    <form method="post" action="">
        <?php wp_nonce_field('uft_add_property', 'uft_nonce'); ?>
        <input type="hidden" name="uft_action" value="add_property">
        <table class="form-table">
            <tr>
                <th><label for="property_name">Property Name</label></th>
                <td><input type="text" id="property_name" name="property_name" required></td>
            </tr>
        </table>
        <?php submit_button('Add Property'); ?>
    </form>
</div>

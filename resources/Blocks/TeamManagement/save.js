import { InnerBlocks } from '@wordpress/block-editor';

const Save = props => {
    const template = () => {
        return (
            <>
                <InnerBlocks.Content />
            </>
        );
    };

    return (
        <div>
            {template()}
        </div>
    );
};
export default Save;

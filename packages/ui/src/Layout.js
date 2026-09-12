export const Layout = ({ children }) => {
	return (
		<div className="bankai-ui-layout" style={{ padding: '20px', maxWidth: '1200px', margin: '0 auto' }}>
			{ children }
		</div>
	);
};

export default Layout;

namespace Ympyrän_ala
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
        }

        private void button1_Click(object sender, EventArgs e)
        {
            try
            {
                double Sade, Vastaus;

                Sade = double.Parse(txtSade.Text);

                Vastaus = Math.PI * Sade;
                txtVastaus.Text = Vastaus.ToString("F2");
            }
            catch (Exception)
            {
                MessageBox.Show("Virhe, syötä tiedot uudelleen");
            }
        }

        private void txtSade_TextChanged(object sender, EventArgs e)
        {

        }
    }
}

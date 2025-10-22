using System.Security.AccessControl;

namespace Pizzat
{
    public partial class Form1 : Form
    {
        int juomat = 0;
        int Pitsat = 0;
        int alennus = 0;
        public Form1()
        {
            InitializeComponent();
        }
        public void poisKäytöstä()//estetään buttoneitten käytön
        {
            button1.Enabled = false;
            button2.Enabled = false;
            button3.Enabled = false;
            button4.Enabled = false;
            button5.Enabled = false;
            button6.Enabled = false;
            button15.Enabled = false;
            button14.Enabled = false;
            button10.Enabled = false;
            button9.Enabled = false;
        }
        public void piilota()//piilotetaan käyttöliittymt ja palautetaa buttonit käyttöön
        {
            label4.Visible = false;
            label6.Visible = false;
            lblHinta.Visible = false;
            label3.Visible = false;
            button13.Visible = false;
            txtTaytteet.Visible = false;
            lblTaytteet.Visible = false;
            checkBox1.Visible = false;
            checkBox2.Visible = false;
            checkBox3.Visible = false;
            checkBox4.Visible = false;
            checkBox5.Visible = false;
            checkBox1.Checked = false;
            checkBox2.Checked = false;
            checkBox3.Checked = false;
            checkBox4.Checked = false;
            checkBox5.Checked = false;

            button1.Enabled = true;
            button2.Enabled = true;
            button3.Enabled = true;
            button4.Enabled = true;
            button5.Enabled = true;
            button6.Enabled = true;
            button15.Enabled = true;
            button14.Enabled = true;
            button10.Enabled = true;
            button9.Enabled = true;
        }
        class pizzat//pizza luokka joka tallentaa tuotteen tiedot
        {
            private int hinta;
            private string Tuote;
            private int maara;

            public pizzat(int hi, string tu, int ma)
            {
                hinta = hi;
                Tuote = tu;
                maara = ma;
            }
            public string tulostaPizza()
            {
                return Tuote;//tulostaa lisätäyte valikkoon pizzan
            }
            public int tulostaHinta()
            {
                return hinta;//tulostaa lisätäyte valikkoon hinnan
            }
            public string tulostaLoppu()
            {
                return Tuote + "............." + hinta + "€" + Environment.NewLine;//tulostaa listalle pizzan ja sen hinnan
            }
            public string tulostaAlennus()
            {
                return Tuote + "............." + (hinta / 2) + "€" + Environment.NewLine;//tulostaa listalle pizzan ja sen hinnan alennetuna
            }
            public string tulostaJuoma()
            {
                return Tuote + "............." + hinta * maara + "€" + Environment.NewLine;//tulostaa listalle juoman ja sen hinnan
            }
        }
        // käsitelee ensimmäisen pizzan tilauksen (pizza1)
        private void button1_Click(object sender, EventArgs e)
        {
            try
            {
                if (Hinta1.Text == "" || int.Parse(Hinta1.Text) < 1) { MessageBox.Show("Lisää kelvollinen arvo hinnalle!"); return; }
                poisKäytöstä();

                int hi = int.Parse(Hinta1.Text);
                string tu = Pizza1.Text;
                int ma = 0;

                pizzat lista = new pizzat(hi, tu, ma);

                txtTaytteet.Text += lista.tulostaPizza();
                lblHinta.Text = lista.tulostaHinta().ToString();

                label6.Visible = true;
                lblHinta.Visible = true;
                label3.Visible = true;
                button13.Visible = true;
                txtTaytteet.Visible = true;
                lblTaytteet.Visible = true;
                checkBox1.Visible = false;
                checkBox2.Visible = false;
                checkBox3.Visible = false;
                checkBox4.Visible = false;
                checkBox5.Visible = false;
            }
            catch (Exception) { MessageBox.Show("Virhe"); }
        }
        private void button2_Click(object sender, EventArgs e) 
        {
            try
            {
                if (Hinta2.Text == "" || int.Parse(Hinta2.Text) < 1) { MessageBox.Show("Lisää kelvollinen arvo hinnalle!"); return; }
                poisKäytöstä();

                int hi = int.Parse(Hinta2.Text);
                string tu = Pizza2.Text;
                int ma = 0;

                pizzat lista = new pizzat(hi, tu, ma);

                txtTaytteet.Text += lista.tulostaPizza();
                lblHinta.Text = lista.tulostaHinta().ToString();

                label4.Visible = true;
                label6.Visible = true;
                lblHinta.Visible = true;
                label3.Visible = true;
                button13.Visible = true;
                txtTaytteet.Visible = true;
                lblTaytteet.Visible = false;
                checkBox1.Visible = true;
                checkBox2.Visible = false;
                checkBox3.Visible = true;
                checkBox4.Visible = false;
                checkBox5.Visible = false;

                checkBox1.Text = "Pepperoni";
                checkBox3.Text = "Sipuli";
            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }//pizza2
        private void button4_Click(object sender, EventArgs e)
        {
            try
            {
                if (Hinta3.Text == "" || int.Parse(Hinta3.Text) < 1) { MessageBox.Show("Lisää kelvollinen arvo hinnalle!"); return; }
                poisKäytöstä();

                int hi = int.Parse(Hinta3.Text);
                string tu = Pizza3.Text;
                int ma = 0;

                pizzat lista = new pizzat(hi, tu, ma);

                txtTaytteet.Text += lista.tulostaPizza();
                lblHinta.Text = lista.tulostaHinta().ToString();

                label4.Visible = true;
                label6.Visible = true;
                lblHinta.Visible = true;
                label3.Visible = true;
                button13.Visible = true;
                txtTaytteet.Visible = true;
                lblTaytteet.Visible = false;
                checkBox1.Visible = true;
                checkBox2.Visible = true;
                checkBox3.Visible = true;
                checkBox4.Visible = false;
                checkBox5.Visible = false;

                checkBox1.Text = "Kana";
                checkBox2.Text = "bbq kastike";
                checkBox3.Text = "Sipuli";
            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }//pizza3
        private void button3_Click(object sender, EventArgs e)
        {
            try
            {
                if (Hinta4.Text == "" || int.Parse(Hinta4.Text) < 1) { MessageBox.Show("Lisää kelvollinen arvo hinnalle!"); return; }
                poisKäytöstä();

                int hi = int.Parse(Hinta4.Text);
                string tu = Pizza4.Text;
                int ma = 0;

                pizzat lista = new pizzat(hi, tu, ma);

                txtTaytteet.Text += lista.tulostaPizza();
                lblHinta.Text = lista.tulostaHinta().ToString();

                label6.Visible = true;
                lblHinta.Visible = true;
                label3.Visible = true;
                button13.Visible = true;
                txtTaytteet.Visible = true;
                lblTaytteet.Visible = false;
                checkBox1.Visible = true;
                checkBox2.Visible = false;
                checkBox3.Visible = true;
                checkBox4.Visible = false;
                checkBox5.Visible = false;

                checkBox1.Text = "Kinkku";
                checkBox3.Text = "Ananas";
            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }//pizza4
        private void button6_Click(object sender, EventArgs e)
        {
            try
            {
                if (Hinta5.Text == "" || int.Parse(Hinta5.Text) < 1) { MessageBox.Show("Lisää kelvollinen arvo hinnalle!"); return; }
                poisKäytöstä();

                int hi = int.Parse(Hinta5.Text);
                string tu = Pizza5.Text;
                int ma = 0;

                pizzat lista = new pizzat(hi, tu, ma);

                txtTaytteet.Text += lista.tulostaPizza();
                lblHinta.Text = lista.tulostaHinta().ToString();

                label4.Visible = true;
                label6.Visible = true;
                lblHinta.Visible = true;
                label3.Visible = true;
                button13.Visible = true;
                txtTaytteet.Visible = true;
                lblTaytteet.Visible = false;
                checkBox1.Visible = true;
                checkBox2.Visible = true;
                checkBox3.Visible = true;
                checkBox4.Visible = false;
                checkBox5.Visible = false;

                checkBox1.Text = "Vuohen juusto";
                checkBox2.Text = "Parmesano";
                checkBox3.Text = "Aura juusto";
            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }//pizza5
        private void button5_Click(object sender, EventArgs e)
        {
            try
            {
                if (Hinta6.Text == "" || int.Parse(Hinta6.Text) < 1) { MessageBox.Show("Lisää kelvollinen arvo hinnalle!"); return; }
                poisKäytöstä();

                int hi = int.Parse(Hinta6.Text);
                string tu = Pizza6.Text;
                int ma = 0;

                pizzat lista = new pizzat(hi, tu, ma);

                txtTaytteet.Text += lista.tulostaPizza();
                lblHinta.Text = lista.tulostaHinta().ToString();

                label4.Visible = true;
                label6.Visible = true;
                lblHinta.Visible = true;
                label3.Visible = true;
                button13.Visible = true;
                txtTaytteet.Visible = true;
                lblTaytteet.Visible = false;
                checkBox1.Visible = true;
                checkBox2.Visible = false;
                checkBox3.Visible = true;
                checkBox4.Visible = false;
                checkBox5.Visible = false;

                checkBox1.Text = "Kana";
                checkBox3.Text = "Ananas";
            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }//pizza6
        private void button15_Click(object sender, EventArgs e)
        {
            try
            {
                if (Hinta7.Text == "" || int.Parse(Hinta7.Text) < 1) { MessageBox.Show("Lisää kelvollinen arvo hinnalle!"); return; }
                poisKäytöstä();

                int hi = int.Parse(Hinta7.Text);
                string tu = Pizza7.Text;
                int ma = 0;

                pizzat lista = new pizzat(hi, tu, ma);

                txtTaytteet.Text += lista.tulostaPizza();
                lblHinta.Text = lista.tulostaHinta().ToString();

                label4.Visible = true;
                label6.Visible = true;
                lblHinta.Visible = true;
                label3.Visible = true;
                button13.Visible = true;
                txtTaytteet.Visible = true;
                lblTaytteet.Visible = false;
                checkBox1.Visible = true;
                checkBox2.Visible = false;
                checkBox3.Visible = true;
                checkBox4.Visible = false;
                checkBox5.Visible = false;

                checkBox1.Text = "Sieni";
                checkBox3.Text = "Sipuli";
            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }//pizza7
        private void button14_Click(object sender, EventArgs e)
        {
            try
            {
                if (Hinta8.Text == "" || int.Parse(Hinta8.Text) < 1) { MessageBox.Show("Lisää kelvollinen arvo hinnalle!"); return; }
                poisKäytöstä();

                int hi = int.Parse(Hinta8.Text);
                string tu = Pizza8.Text;
                int ma = 0;

                pizzat lista = new pizzat(hi, tu, ma);

                txtTaytteet.Text += lista.tulostaPizza();
                lblHinta.Text = lista.tulostaHinta().ToString();

                label4.Visible = true;
                label6.Visible = true;
                lblHinta.Visible = true;
                label3.Visible = true;
                button13.Visible = true;
                txtTaytteet.Visible = true;
                lblTaytteet.Visible = false;
                checkBox1.Visible = true;
                checkBox2.Visible = true;
                checkBox3.Visible = true;
                checkBox4.Visible = true;
                checkBox5.Visible = true;

                checkBox1.Text = "Pepperoni";
                checkBox2.Text = "Jauheliha";
                checkBox3.Text = "Sipuli";
                checkBox4.Text = "pekoni";
                checkBox5.Text = "bbq kastike";

            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }//pizza8
        private void button10_Click(object sender, EventArgs e)
        {
            try
            {
                if (Hinta9.Text == "" || int.Parse(Hinta9.Text) < 1 ) { MessageBox.Show("Lisää kelvollinen arvo hinnalle!"); return; }
                poisKäytöstä();

                int hi = int.Parse(Hinta9.Text);
                string tu = Pizza9.Text;
                int ma = 0;

                pizzat lista = new pizzat(hi, tu, ma);

                txtTaytteet.Text += lista.tulostaPizza();
                lblHinta.Text = lista.tulostaHinta().ToString();

                label4.Visible = true;
                label6.Visible = true;
                lblHinta.Visible = true;
                label3.Visible = true;
                button13.Visible = true;
                txtTaytteet.Visible = true;
                lblTaytteet.Visible = false;
                checkBox1.Visible = true;
                checkBox2.Visible = true;
                checkBox3.Visible = true;
                checkBox4.Visible = false;
                checkBox5.Visible = false;

                checkBox1.Text = "Paprika";
                checkBox2.Text = "tomaatti";
                checkBox3.Text = "Sipuli";

            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }//pizza9
        private void button9_Click(object sender, EventArgs e)
        {
            try
            {
                if (Hinta10.Text == "" || int.Parse(Hinta10.Text) < 1) { MessageBox.Show("Lisää kelvollinen arvo hinnalle!"); return; }
                poisKäytöstä();

                int hi = int.Parse(Hinta10.Text);
                string tu = Pizza10.Text;
                int ma = 0;

                pizzat lista = new pizzat(hi, tu, ma);

                txtTaytteet.Text += lista.tulostaPizza();
                lblHinta.Text = lista.tulostaHinta().ToString();

                label6.Visible = true;
                lblHinta.Visible = true;
                label3.Visible = true;
                button13.Visible = true;
                txtTaytteet.Visible = true;
                lblTaytteet.Visible = false;
                checkBox1.Visible = true;
                checkBox2.Visible = true;
                checkBox3.Visible = true;
                checkBox4.Visible = false;
                checkBox5.Visible = false;

                checkBox1.Text = "Kinkku";
                checkBox2.Text = "Aurajuusto";
                checkBox3.Text = "Ananas";

            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }//pizza10

        //Lisää juoman lopulliseen listaan (juoma1)
        private void button11_Click(object sender, EventArgs e)
        {
            try
            {
                if (nmbMaara1.Text == "" || int.Parse(nmbMaara1.Text) < 1) { MessageBox.Show("Lisää kelvollinen arvo hinnalle!"); return; }
                int hi = int.Parse(HintaJ1.Text);
                string tu = txtJuoma1.Text;
                int ma = int.Parse(nmbMaara1.Text);

                pizzat lista = new pizzat(hi, tu, ma);

                txtJuomat.Clear();
                juomat += hi * ma;

                txtLista.Text += lista.tulostaJuoma();
                txtJuomat.Text += juomat;

                int pitsat = 0, lisaAineet = 0, juomat1 = 0;

                int.TryParse(txtLisaTaytteet.Text, out lisaAineet);
                int.TryParse(txtPitsat.Text, out pitsat);
                int.TryParse(txtJuomat.Text, out juomat1);

                int summa = lisaAineet + pitsat + juomat1;
                txtSumma.Text = summa.ToString();
            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }
        
        private void button12_Click(object sender, EventArgs e) 
        {
            try
            {
                if (nmbMaara2.Text == "" || int.Parse(nmbMaara2.Text)< 1) { MessageBox.Show("Lisää kelvollinen arvo hinnalle!"); return; }//juoma2
                int hi = int.Parse(HintaJ2.Text);
                string tu = txtJuoma2.Text;
                int ma = int.Parse(nmbMaara2.Text);

                pizzat lista = new pizzat(hi, tu, ma);

                txtJuomat.Clear();
                juomat += hi * ma;

                txtLista.Text += lista.tulostaJuoma();
                txtJuomat.Text += juomat;

                int pitsat = 0, lisaAineet = 0, juomat1 = 0;

                int.TryParse(txtLisaTaytteet.Text, out lisaAineet);
                int.TryParse(txtPitsat.Text, out pitsat);
                int.TryParse(txtJuomat.Text, out juomat1);

                int summa = lisaAineet + pitsat + juomat1;
                txtSumma.Text = summa.ToString();

            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }
        // Käsittelee tilauksen lisäämisen lopulliseen listaan ja alennuksen 
        private void button13_Click_1(object sender, EventArgs e)
        {
            try
            {
                alennus++;

                int phi = int.Parse(lblHinta.Text);
                int hi = int.Parse(lblHinta.Text);
                string tu = txtTaytteet.Text;
                int ma = 0;

                int lisalist = 0;
                if (checkBox1.Checked) { hi += +2; lisalist += 2; }
                if (checkBox2.Checked) { hi += +2; lisalist += 2; }
                if (checkBox3.Checked) { hi += +2; lisalist += 2; }
                if (checkBox4.Checked) { hi += +2; lisalist += 2; }
                if (checkBox5.Checked) { hi += +2; lisalist += 2; }

                pizzat lista = new pizzat(hi, tu, ma);

                if (alennus % 6 == 0) { phi = phi / 2; txtLista.Text += lista.tulostaAlennus(); }
                else { txtLista.Text += lista.tulostaLoppu(); }

                Pitsat += phi;

                txtLisaTaytteet.Text = lisalist.ToString();
                txtPitsat.Text = Pitsat.ToString();

                txtTaytteet.Clear();
                piilota();

                int pitsat = 0, lisaAineet = 0, juomat = 0;

                //koko tilauksen summa
                int.TryParse(txtLisaTaytteet.Text, out lisaAineet);
                int.TryParse(txtPitsat.Text, out pitsat);
                int.TryParse(txtJuomat.Text, out juomat);

                int summa = lisaAineet + pitsat + juomat;
                txtSumma.Text = summa.ToString();
            }
            catch (Exception) { MessageBox.Show("Virhe!"); }
        }
        //Päivän tulot
        private void button7_Click_1(object sender, EventArgs e)
        {
            try
            {
                int pitsat = 0, lisaAineet = 0, juomat = 0;

                int.TryParse(txtLisaTaytteet.Text, out lisaAineet);
                int.TryParse(txtPitsat.Text, out pitsat);
                int.TryParse(txtJuomat.Text, out juomat);

                int summa = lisaAineet + pitsat + juomat;
                txtSumma.Text = summa.ToString();
                txtKertynyt.Text = summa.ToString();

            }
            catch (Exception) { MessageBox.Show("Virhe"); }
        }
        //poistetaan viimeisen listan 
        private void button8_Click(object sender, EventArgs e)
        {
            piilota();
            txtLista.Clear();
            txtSumma.Clear();
            txtJuomat.Clear();
            txtLisaTaytteet.Clear();
            txtPitsat.Clear();
        }
        private void button7_Click(object sender, EventArgs e)//Vahinko
        {

        }
        private void button13_Click(object sender, EventArgs e)//Vahinko
        {

        }
        private void Form1_Load(object sender, EventArgs e)//Vahinko
        {

        }


    }
}

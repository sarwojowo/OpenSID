<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Dpt extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('penduduk_model');
		$this->load->model('dpt_model');
		$this->load->model('referensi_model');
		$this->load->model('wilayah_model');
		$this->load->model('header_model');
		$this->modul_ini = 2;
		$this->sub_modul_ini = 26;
	}

	public function clear()
	{
		$list_session = array('cari', 'sex', 'dusun', 'rw', 'rt', 'tanggal_pemilihan', 'umurx', 'umur_min', 'umur_max', 'cacatx', 'menahunx', 'pekerjaan_id', 'status', 'agama', 'pendidikan_sedang_id', 'pendidikan_kk_id', 'status_penduduk');

		$this->session->unset_userdata($list_session);
		$_SESSION['per_page'] = 50;
		redirect('dpt');
	}

	public function index($p=1, $o=0)
	{
		$data['p'] = $p;
		$data['o'] = $o;

		// Session yg diload index
		$list_session = array('cari', 'sex', 'tanggal_pemilihan');

		foreach ($list_session as $session)
		{
			$data[$session] = $this->session->userdata($session) ?: '';
		}

		if (isset($_SESSION['dusun']))
		{
			$data['dusun'] = $_SESSION['dusun'];
			$data['list_rw'] = $this->wilayah_model->list_rw($data['dusun']);

			if (isset($_SESSION['rw']))
			{
				$data['rw'] = $_SESSION['rw'];
				$data['list_rt'] = $this->wilayah_model->list_rt($data['dusun'], $data['rw']);
				if (isset($_SESSION['rt']))
					$data['rt'] = $_SESSION['rt'];
				else $data['rt'] = '';
			}
			else $data['rw'] = '';
		}
		else
		{
			$data['dusun'] = '';
			$data['rw'] = '';
			$data['rt'] = '';
		}

		if (isset($_POST['per_page']))
			$data['per_page'] = $_SESSION['per_page'] = $_POST['per_page'];

		$data['list_jenis_kelamin'] = $this->referensi_model->list_data('tweb_penduduk_sex');
		$data['list_dusun'] = $this->wilayah_model->list_dusun();

		$data['paging'] = $this->dpt_model->paging($p, $o);
		$data['main'] = $this->dpt_model->list_data($o, $data['paging']->offset, $data['paging']->per_page);
		$data['keyword'] = $this->dpt_model->autocomplete();

		$header = $this->header_model->get_data();
		$header['minsidebar'] = 1;

		$this->load->view('header', $header);
		$this->load->view('nav',$nav);
		$this->load->view('dpt/dpt', $data);
		$this->load->view('footer');
	}

	public function search()
	{
		$cari = $this->input->post('cari');
		if ($cari != '')
			$_SESSION['cari']=$cari;
		else unset($_SESSION['cari']);
		redirect('dpt');
	}

	public function sex($p=1, $o=0)
	{
		$sex = $this->input->post('sex');
		if ($sex != "")
			$_SESSION['sex'] = $sex;
		else unset($_SESSION['sex']);
		redirect("dpt/index/$p/$o");
	}

	public function dusun($p=1, $o=0)
	{
		unset($_SESSION['rw']);
		unset($_SESSION['rt']);
		$dusun = $this->input->post('dusun');
		if ($dusun != "")
			$_SESSION['dusun'] = $dusun;
		else unset($_SESSION['dusun']);
		redirect("dpt/index/$p/$o");
	}

	public function rw($p=1, $o=0)
	{
		unset($_SESSION['rt']);
		$rw = $this->input->post('rw');
		if ($rw != "")
			$_SESSION['rw'] = $rw;
		else unset($_SESSION['rw']);
		redirect("dpt/index/$p/$o");
	}

	public function rt($p=1, $o=0)
	{
		$rt = $this->input->post('rt');
		if ($rt != "")
			$_SESSION['rt'] = $rt;
		else unset($_SESSION['rt']);
		redirect("dpt/index/$p/$o");
	}

	public function ajax_adv_search()
	{
		$list_session = array('umur_min', 'umur_max', 'pekerjaan_id', 'status', 'agama', 'pendidikan_sedang_id', 'pendidikan_kk_id', 'status_penduduk');

		foreach ($list_session as $session)
		{
			$data[$session] = $this->session->userdata($session) ?: '';
		}

		$data['list_agama'] = $this->referensi_model->list_data('tweb_penduduk_agama');
		$data['list_pendidikan'] = $this->referensi_model->list_data('tweb_penduduk_pendidikan');
		$data['list_pendidikan_kk'] = $this->referensi_model->list_data('tweb_penduduk_pendidikan_kk');
		$data['list_pekerjaan'] = $this->referensi_model->list_data('tweb_penduduk_pekerjaan');
		$data['list_status_kawin'] = $this->referensi_model->list_data('tweb_penduduk_kawin');
		$data['list_status_penduduk'] = $this->referensi_model->list_data('tweb_penduduk_status');
		$data['form_action'] = site_url("dpt/adv_search_proses");

		$this->load->view("sid/kependudukan/ajax_adv_search_form", $data);
	}

	public function adv_search_proses()
	{
		$adv_search = $_POST;
		$i = 0;
		while ($i++ < count($adv_search))
		{
			$col[$i] = key($adv_search);
				next($adv_search);
		}
		$i = 0;
		while ($i++ < count($col))
		{
			if ($adv_search[$col[$i]] == "")
			{
				UNSET($adv_search[$col[$i]]);
				UNSET($_SESSION[$col[$i]]);
			}
			else
			{
				$_SESSION[$col[$i]] = $adv_search[$col[$i]];
			}
		}
		redirect("dpt/index/1/$o");
	}

	public function cetak($o=0)
	{
		$data['main'] = $this->dpt_model->list_data($o, 0, 10000);
		$this->load->view('dpt/dpt_print', $data);
	}

	public function excel($o=0)
	{
		$data['main'] = $this->dpt_model->list_data($o, 0, 10000);
		$this->load->view('dpt/dpt_excel', $data);
	}

}
